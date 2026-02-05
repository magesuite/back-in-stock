<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Service;

class NotificationQueueSenderTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollection;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection $subscriptionCollection;
    protected ?\MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender;
    protected ?\MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender $emailNotificationSender;
    protected ?\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $backInStockSubscriptionCollectionFactory;

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
        $this->backInStockSubscriptionCollectionFactory = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItRemovesNotificationsAfterQueueIsProcessed(): void
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
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItMarkSubscriptionsAsRemovedAfterQueueIsProcessed(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(10, $notificationCollection->getSize());

        $this->notificationQueueSender->send(true);

        $notificationCollection = $this->notificationCollection->create();

        $removedSubscriptions = $this->backInStockSubscriptionCollectionFactory->create()
            ->addFieldToFilter('is_removed', 1);

        $this->assertEquals(10, $removedSubscriptions->getSize());
        $this->assertEquals(0, $notificationCollection->getSize());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_marked_removed.php
    */
    public function testItGetsCorrectDataToSendForAutomaticType(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(7, $notificationCollection->getSize());

        $emailNotificationSender = $this->emailNotificationSender;

        foreach ($notificationCollection as $notification) {
            $subscription = $this->backInStockSubscriptionRepository->getById((int)$notification->getSubscriptionId());

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
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_marked_removed.php
     */
    public function testItGetCorrectDataToSendForManualType(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection->create();

        $this->assertEquals(7, $notificationCollection->getSize());

        $emailNotificationSender = $this->emailNotificationSender;

        foreach ($notificationCollection as $notification) {
            $subscription = $this->backInStockSubscriptionRepository->getById((int)$notification->getSubscriptionId());

            $this->assertEquals('back_in_stock/email_configuration/manual_notification_email_template', $emailNotificationSender->getEmailTemplateId($notification->getNotificationType()));

            $emailParams = $emailNotificationSender->getTemplateParams($notification, $subscription);

            $this->assertEquals('test message', $emailParams['notification_message']);
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoConfigFixture admin_store back_in_stock/limits/min_time 0
     */
    public function testNotificationSendWithoutMinTimeLimit(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        for ($i = 0; $i < 2; $i++) {
            $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');
            $this->notificationQueueSender->send(false);
        }

        foreach ($this->subscriptionCollection as $subscription) {
            $this->assertNotNull($subscription->getSendNotificationStatus());
            $this->assertEquals(2, $subscription->getSendCount());
        }
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoConfigFixture admin_store back_in_stock/limits/min_time 3600
     */
    public function testNotificationSendWithMinTimeLimit(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        for ($i = 0; $i < 2; $i++) {
            $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');
            $this->notificationQueueSender->send(false);
        }

        foreach ($this->subscriptionCollection as $subscription) {
            $this->assertNotNull($subscription->getSendNotificationStatus());
            $this->assertEquals(1, $subscription->getSendCount());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoConfigFixture admin_store back_in_stock/limits/daily_limit 0
     */
    public function testNotificationSendWithoutDailyLimitCount(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        for ($i = 0; $i < 3; $i++) {
            $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');
            $this->notificationQueueSender->send(false);
        }

        foreach ($this->subscriptionCollection as $subscription) {
            $this->assertNotNull($subscription->getSendNotificationStatus());
            $this->assertEquals(3, $subscription->getSendCount());
            $this->assertEquals(3, $subscription->getSendCountDaily());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoConfigFixture admin_store back_in_stock/limits/daily_limit 2
     */
    public function testNotificationSendWithDailyLimitCount(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        for ($i = 0; $i < 3; $i++) {
            $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');
            $this->notificationQueueSender->send(false);
        }

        foreach ($this->subscriptionCollection as $subscription) {
            $this->assertNotNull($subscription->getSendNotificationStatus());
            $this->assertEquals(2, $subscription->getSendCount());
            $this->assertEquals(2, $subscription->getSendCountDaily());
        }
    }
}
