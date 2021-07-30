<?php

namespace MageSuite\BackInStock\Test\Integration\Service;

class NotificationQueueSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueCreator
     */
    protected $notificationQueueCreator;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollection;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection
     */
    protected $subscriptionCollection;

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueSender
     */
    protected $notificationQueueSender;

    /**
     * @var \MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender
     */
    protected $emailNotificationSender;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->notificationQueueCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueCreator::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory::class);
        $this->subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
        $this->notificationQueueSender = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueSender::class);
        $this->emailNotificationSender = $this->objectManager->create(\MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender::class);
        $this->backInStockSubscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     */
    public function testItRemovesNotificationsAfterQueueIsProcessed()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $notificationCollection->getSize());

        $this->notificationQueueSender->send(false);

        foreach ($this->subscriptionCollection as $subscription) {
            $this->assertNotNull($subscription->getSendNotificationStatus());
            $this->assertEquals(1, $subscription->getSendCount());
        }

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $this->subscriptionCollection->getSize());
        $this->assertEquals(0, $notificationCollection->getSize());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     */
    public function testItRemovesSubscriptionsAfterQueueIsProcessed()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $notificationCollection->getSize());

        $this->notificationQueueSender->send(true);

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(0, $this->subscriptionCollection->getSize());
        $this->assertEquals(0, $notificationCollection->getSize());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     */
    public function testItGetsCorrectDataToSendForAutomaticType()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $notificationCollection->getSize());

        $emailNotificationSender = $this->emailNotificationSender;

        foreach ($notificationCollection as $notification) {
            $subscription = $this->backInStockSubscriptionRepository->getById($notification->getSubscriptionId());

            $this->assertEquals('back_in_stock/email_configuration/automatic_notification_email_template', $emailNotificationSender->getEmailTemplateId($notification->getNotificationType()));

            $emailParams = $emailNotificationSender->getTemplateParams($notification, $subscription);

            $this->assertEquals('Simple Product', $emailParams['product_name']);
            $this->assertEquals('simple', $emailParams['product_sku']);
            $this->assertEquals('http://localhost/index.php/simple-product.html', $emailParams['product_url']);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     */
    public function testItGetCorrectDataToSendForManualType()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $notificationCollection->getSize());

        $emailNotificationSender = $this->emailNotificationSender;

        foreach ($notificationCollection as $notification) {
            $subscription = $this->backInStockSubscriptionRepository->getById($notification->getSubscriptionId());

            $this->assertEquals('back_in_stock/email_configuration/manual_notification_email_template', $emailNotificationSender->getEmailTemplateId($notification->getNotificationType()));

            $emailParams = $emailNotificationSender->getTemplateParams($notification, $subscription);

            $this->assertEquals('test message', $emailParams['notification_message']);
        }
    }

    public static function loadSubscriptions()
    {
        include __DIR__ . '/../../_files/subscriptions.php';
    }

    public static function loadSubscriptionsCustomerConfirmed()
    {
        include __DIR__ . '/../../_files/subscriptions_confirmed_customer.php';
    }
}
