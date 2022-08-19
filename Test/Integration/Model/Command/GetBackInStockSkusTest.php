<?php

namespace MageSuite\BackInStock\Test\Integration\Model\Command;

class GetBackInStockSkusTest extends \PHPUnit\Framework\TestCase
{
    const SOURCE_CODE_DEFAULT = 'default';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory
     */
    protected $sourceItemFactory;

    /**
     * @var \MageSuite\BackInStock\Model\Command\GetBackInStockItems
     */
    protected $getBackInStockItems;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->sourceItemFactory = $objectManager->get(\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory::class);
        $this->getBackInStockItems = $objectManager->get(\MageSuite\BackInStock\Model\Command\GetBackInStockItems::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItDoesNotAddSkuToQueueIfProductIsOutStock()
    {
        $productSku = 'simple';

        $sourceItem = $this->prepareSourceItem($productSku, 100, \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK);
        $items = $this->getBackInStockItems->execute([$sourceItem]);

        $this->assertEmpty($items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture productOutOfStock
     */
    public function testItAddsSkuToQueueIfProductIsOutOfStock()
    {
        $productSku = 'product_out_of_stock';
        $newQty = 100;

        $sourceItem = $this->prepareSourceItem($productSku, $newQty, \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK);
        $items = $this->getBackInStockItems->execute([$sourceItem]);

        $this->assertNotEmpty($items);
        $this->assertArrayHasKey($productSku, $items);
        $this->assertArrayHasKey(self::SOURCE_CODE_DEFAULT, $items[$productSku]);
        $this->assertEquals(0.0000, $items[$productSku][self::SOURCE_CODE_DEFAULT]['old_qty']);
        $this->assertEquals($newQty, $items[$productSku][self::SOURCE_CODE_DEFAULT]['new_qty']);
        $this->assertEquals(\Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK, $items[$productSku][self::SOURCE_CODE_DEFAULT]['old_status']);
    }

    protected function prepareSourceItem($productSku, $quantity, $status)
    {
        return $this->sourceItemFactory->create(
            [
                'data' => [
                    \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE => self::SOURCE_CODE_DEFAULT,
                    \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => $productSku,
                    \Magento\InventoryApi\Api\Data\SourceItemInterface::QUANTITY => $quantity,
                    \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS => $status
                ]
            ]
        );
    }

    public static function productOutOfStock()
    {
        include __DIR__ . '/../../../_files/product_out_of_stock.php';
    }

    public static function productOutOfStockRollback()
    {
        include __DIR__ . '/../../../_files/product_out_of_stock_rollback.php';
    }
}
