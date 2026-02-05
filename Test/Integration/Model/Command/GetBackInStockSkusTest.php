<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Model\Command;

class GetBackInStockSkusTest extends \PHPUnit\Framework\TestCase
{
    protected const SOURCE_CODE_DEFAULT = 'default';
    protected const STOCK_DEFAULT_ID = 1;

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory;
    protected ?\MageSuite\BackInStock\Model\Command\GetBackInStockItems $getBackInStockItems;
    protected ?\PHPUnit\Framework\MockObject\MockObject $getSalableStatusesStub;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->sourceItemFactory = $objectManager->get(\Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory::class);
        $this->getSalableStatusesStub = $this->getMockBuilder(\MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->getBackInStockItems = $this->objectManager->create(
            \MageSuite\BackInStock\Model\Command\GetBackInStockItems::class,
            ['getSalableStatuses' => $this->getSalableStatusesStub]
        );
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItDoesNotAddSkuToQueueIfProductIsOutStock(): void
    {
        $productSku = 'simple';

        $sourceItem = $this->prepareSourceItem($productSku, 100, \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK);
        $items = $this->getBackInStockItems->execute([$sourceItem]);

        $this->assertEmpty($items);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/product_out_of_stock.php
     */
    public function testItAddsSkuToQueueIfProductIsOutOfStock(): void
    {
        $productSku = 'product_out_of_stock';
        $newQty = 100;

        $statuses = [
            $productSku => [
                self::STOCK_DEFAULT_ID => false
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        $sourceItem = $this->prepareSourceItem($productSku, $newQty, \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK);
        $items = $this->getBackInStockItems->execute([$sourceItem]);

        $this->assertNotEmpty($items);
        $this->assertArrayHasKey($productSku, $items);
        $this->assertArrayHasKey('source_items', $items[$productSku]);
        $this->assertArrayHasKey(self::SOURCE_CODE_DEFAULT, $items[$productSku]['source_items']);
        $this->assertEquals(0.0000, $items[$productSku]['source_items'][self::SOURCE_CODE_DEFAULT]['old_qty']);
        $this->assertEquals($newQty, $items[$productSku]['source_items'][self::SOURCE_CODE_DEFAULT]['new_qty']);
        $this->assertEquals(\Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK, $items[$productSku]['source_items'][self::SOURCE_CODE_DEFAULT]['old_status']);
    }

    protected function prepareSourceItem(string $productSku, int $quantity, int $status): \Magento\InventoryApi\Api\Data\SourceItemInterface
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
}
