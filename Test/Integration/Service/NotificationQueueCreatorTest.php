<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Service;

class NotificationQueueCreatorTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection $notificationCollection;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->notificationQueueCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueCreator::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItCreateNotificationQueueCorrectly(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection;

        $this->assertEquals(10, $notificationCollection->getSize());

        foreach ($notificationCollection as $notification) {
            $this->assertEquals('automatic_notification', $notification->getNotificationType());
            $this->assertEquals('test message', $notification->getMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/removed_subscriptions.php
     */
    public function testItDoesNotAddRemovedSubscriptionToNotificationQueue(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection;

        $this->assertEquals(0, $notificationCollection->getSize());
    }
}
