<?php

namespace MageSuite\BackInStock\Test\Integration\Service;

class SubscriptionEntityCreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Service\SubscriptionEntityCreator
     */
    protected $subscriptionEntityCreator;

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueCreator
     */
    protected $notificationQueueCreator;

    /**
     * @var \MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender
     */
    protected $notificationQueueSender;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollection;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection
     */
    protected $subscriptionCollection;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->subscriptionEntityCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\SubscriptionEntityCreator::class);
        $this->notificationQueueCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueCreator::class);
        $this->notificationQueueSender = $this->objectManager->create(\MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     */
    public function testItSubscribeCorrectly()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->subscriptionEntityCreator->subscribe(['notification_channel' => 'email', 'product' => $product->getId(), 'email' => 'test_email@test.com']);

        $subscriptionCollection = $this->subscriptionCollection
            ->addFieldToFilter('product_id', ['eq' => $product->getId()])
            ->addFieldToFilter('customer_email', ['eq' => 'test_email@test.com']);

        $this->assertEquals(1, $subscriptionCollection->getSize());

        $subscription = $subscriptionCollection->getFirstItem();

        $this->assertEquals('test_email@test.com', $subscription->getCustomerEmail());
        $this->assertEquals($product->getId(), $subscription->getProductId());
    }

    public static function loadSubscriptions()
    {
        include __DIR__.'/../../_files/subscriptions.php';
    }
}
