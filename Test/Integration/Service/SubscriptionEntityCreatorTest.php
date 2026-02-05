<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Service;

class SubscriptionEntityCreatorTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\BackInStock\Service\SubscriptionEntityCreator $subscriptionEntityCreator;
    protected ?\MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator;
    protected ?\MageSuite\BackInStock\Service\Notification\Sender\Channel\EmailNotificationSender $notificationQueueSender;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollection;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection $subscriptionCollection;

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
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     */
    public function testItSubscribeCorrectly(): void
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
}
