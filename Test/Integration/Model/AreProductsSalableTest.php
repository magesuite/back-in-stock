<?php
namespace MageSuite\BackInStock\Test\Integration\Model;

class AreProductsSalableTest extends \PHPUnit\Framework\TestCase
{
    const PRODUCT_SKU = 'simple';
    const STOCK_ID = 1;

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
    protected $resourceModelStub;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->resourceModelStub = $this->getMockBuilder(\MageSuite\BackInStock\Model\ResourceModel\Stock::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areProductsSalable = $this->objectManager->create(
            \MageSuite\BackInStock\Model\AreProductsSalable::class,
            ['resourceModel' => $this->resourceModelStub]
        );
    }

    /**
     * @param array $stockInfo
     * @param array $expectedData
     * @dataProvider dataProvider
     */
    public function testItReturnsCorrectInformationData($stockInfo, $expectedData)
    {
        $stockData = $this->prepareStockData($stockInfo);
        $this->resourceModelStub->method('getStockDataForSkus')->willReturn($stockData);

        $backInStockItems = [
            self::PRODUCT_SKU => [
                self::STOCK_ID => [
                    'old_qty' => $stockInfo['old_qty'],
                    'new_qty' => $stockInfo['new_qty'],
                    'old_status' => $stockInfo['old_status']
                ]
            ]
        ];

        $areProductsSalable = $this->areProductsSalable->execute([self::PRODUCT_SKU], $backInStockItems);
        $isProductSalable = $areProductsSalable[self::PRODUCT_SKU][self::STOCK_ID];

        $this->assertEquals($expectedData['is_salable'], $isProductSalable->isSalable());
        $this->assertEquals($expectedData['was_salable'], $isProductSalable->wasSalable());
    }

    protected function prepareStockData($stockInfo)
    {
        $stockQtys[self::PRODUCT_SKU][self::STOCK_ID] = $stockInfo['new_qty'];
        $reservation[self::PRODUCT_SKU][self::STOCK_ID] = $stockInfo['reservation_qty'];
        $minimumQtys[self::PRODUCT_SKU] = $stockInfo['minimum_qty'];

        return new \Magento\Framework\DataObject([
            'stock_qtys' => $stockQtys,
            'reservations' => $reservation,
            'minimum_qtys' => $minimumQtys
        ]);
    }

    public function dataProvider()
    {
        $outOfStock = \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK;
        $inStock = \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK;

        return [
            [['old_qty' => 0, 'new_qty' => 1, 'old_status' => $outOfStock, 'reservation_qty' => 0, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 0, 'new_qty' => 5, 'old_status' => $outOfStock, 'reservation_qty' => 0, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 1, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 1], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 2, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 1], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 1, 'old_status' => $outOfStock, 'reservation_qty' => -1, 'minimum_qty' => 1], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 2, 'old_status' => $outOfStock, 'reservation_qty' => -1, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 0, 'new_qty' => 1, 'old_status' => $outOfStock, 'reservation_qty' => 0, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 3, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 4, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 3, 'new_qty' => 3, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 3, 'new_qty' => 4, 'old_status' => $outOfStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 0, 'new_qty' => 1, 'old_status' => $inStock, 'reservation_qty' => 0, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 2, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 1], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 3, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 1, 'old_status' => $inStock, 'reservation_qty' => -1, 'minimum_qty' => 1], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 2, 'old_status' => $inStock, 'reservation_qty' => -1, 'minimum_qty' => 1], ['is_salable' => true, 'was_salable' => true]],
            [['old_qty' => 0, 'new_qty' => 1, 'old_status' => $inStock, 'reservation_qty' => 0, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 0, 'new_qty' => 2, 'old_status' => $inStock, 'reservation_qty' => 0, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 3, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 2, 'new_qty' => 4, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 3, 'new_qty' => 3, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 3, 'new_qty' => 4, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => false]],
            [['old_qty' => 4, 'new_qty' => 3, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => false, 'was_salable' => false]],
            [['old_qty' => 4, 'new_qty' => 4, 'old_status' => $inStock, 'reservation_qty' => -2, 'minimum_qty' => 2], ['is_salable' => true, 'was_salable' => true]]
        ];
    }
}
