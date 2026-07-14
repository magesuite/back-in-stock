<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Model;

class AreProductsSalableTest extends \PHPUnit\Framework\TestCase
{
    protected const PRODUCT_SKU = 'simple';
    protected const STOCK_ID = 1;
    protected const DEFAULT_SOURCE = 'default';

    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\BackInStock\Model\AreProductsSalable $areProductsSalable;
    protected ?\PHPUnit\Framework\MockObject\MockObject $getSalableStatusesStub;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->getSalableStatusesStub = $this->getMockBuilder(\MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areProductsSalable = $this->objectManager->create(
            \MageSuite\BackInStock\Model\AreProductsSalable::class,
            ['getSalableStatuses' => $this->getSalableStatusesStub]
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testItReturnsCorrectInformationData(array $stockInfo, array $expectedData): void
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
                ]
            ]
        ];

        $areProductsSalable = $this->areProductsSalable->execute([self::PRODUCT_SKU], $backInStockItems);
        $isProductSalable = $areProductsSalable[self::PRODUCT_SKU][self::STOCK_ID];

        $this->assertEquals($expectedData['is_salable'], $isProductSalable->isSalable());
        $this->assertEquals($expectedData['was_salable'], $isProductSalable->wasSalable());
    }

    public static function dataProvider(): array
    {
        return [
            [['salable_status_before' => false, 'salable_status_after' => true], ['is_salable' => true, 'was_salable' => false]],
            [['salable_status_before' => false, 'salable_status_after' => false], ['is_salable' => false, 'was_salable' => false]],
            [['salable_status_before' => true, 'salable_status_after' => false], ['is_salable' => false, 'was_salable' => true]],
            [['salable_status_before' => true, 'salable_status_after' => true], ['is_salable' => true, 'was_salable' => true]],
        ];
    }
}
