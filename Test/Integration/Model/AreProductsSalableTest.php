<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Model;

class AreProductsSalableTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_SKU = 'simple';
    const STOCK_ID = 1;
    const DEFAULT_SOURCE = 'default';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Model\AreProductsSalable
     */
    protected $areProductsSalable;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject
     */
    protected $getSalableStatusesStub;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->getSalableStatusesStub = $this->getMockBuilder(\Magento\InventoryIndexer\Indexer\SourceItem\GetSalableStatuses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areProductsSalable = $this->objectManager->create(
            \MageSuite\BackInStock\Model\AreProductsSalable::class,
            ['getSalableStatuses' => $this->getSalableStatusesStub]
        );
    }

    /**
     * @param array $stockInfo
     * @param array $expectedData
     * @dataProvider dataProvider
     */
    public function testItReturnsCorrectInformationData(array $stockInfo, array $expectedData)
    {
        $statuses = [
            self::PRODUCT_SKU => [
                self::STOCK_ID => $stockInfo['salable_status_after']
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        $backInStockItems = [
            self::PRODUCT_SKU => [
                'salable_status_before' => [
                    self::STOCK_ID => $stockInfo['salable_status_before']
                ],
                'source_items' => [
                    self::DEFAULT_SOURCE => [
                        'item_id' => $stockInfo['item_id'],
                    ]
                ]
            ]
        ];

        $areProductsSalable = $this->areProductsSalable->execute([self::PRODUCT_SKU], $backInStockItems);
        $isProductSalable = $areProductsSalable[self::PRODUCT_SKU][self::STOCK_ID];

        $this->assertEquals($expectedData['is_salable'], $isProductSalable->isSalable());
        $this->assertEquals($expectedData['was_salable'], $isProductSalable->wasSalable());
    }

    public function dataProvider(): array
    {
        return [
            [['item_id' => 123, 'salable_status_before' => false, 'salable_status_after' => true], ['is_salable' => true, 'was_salable' => false]],
            [['item_id' => 123, 'salable_status_before' => false, 'salable_status_after' => false], ['is_salable' => false, 'was_salable' => false]],
            [['item_id' => 123, 'salable_status_before' => true, 'salable_status_after' => false], ['is_salable' => false, 'was_salable' => true]],
            [['item_id' => 123, 'salable_status_before' => true, 'salable_status_after' => true], ['is_salable' => true, 'was_salable' => true]],
        ];
    }
}
