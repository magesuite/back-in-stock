<?php

namespace MageSuite\BackInStock\Test\Integration\Model\Queue\Handler;

class AddNotificationToQueueTest extends \PHPUnit\Framework\TestCase
{
    const SOURCE_CODE_DEFAULT = 'default';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;

    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    protected ?\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection $notificationCollection;

    protected ?\MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue $addNotificationToQueue;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);

        $this->addNotificationToQueue = $this->objectManager->create(\MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_marked_removed.php
     */
    public function testItCreateNotificationQueueCorrectly()
    {
        $productSku = 'simple';

        $item = [
            $productSku => [
                self::SOURCE_CODE_DEFAULT => [
                    'old_qty' => 0,
                    'new_qty' => 10,
                    'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK
                ]
            ]
        ];

        $this->assertEquals(0, $this->notificationCollection->getSize());

        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(7, $this->notificationCollection->getSize());
        foreach ($this->notificationCollection->getItems() as $notification) {
            $this->assertEquals('automatic_notification', $notification->getNotificationType());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_marked_removed.php
     */
    public function testItNotCreateNotificationQueueForDisabledProduct()
    {
        $productSku = 'simple';
        $product = $this->productRepository->get($productSku);
        $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
        $this->productRepository->save($product);

        $item = [
            $productSku => [
                self::SOURCE_CODE_DEFAULT => [
                    'old_qty' => 0,
                    'new_qty' => 10,
                    'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK
                ]
            ]
        ];

        $this->assertEquals(0, $this->notificationCollection->getSize());

        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(0, $this->notificationCollection->getSize());
    }
}
