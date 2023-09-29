<?php

namespace MageSuite\BackInStock\Model;

class AreProductsSalable implements \MageSuite\BackInStock\Api\AreProductsSalableInterface
{
    protected \MageSuite\BackInStock\Model\ResourceModel\Stock $resourceModel;

    protected \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterfaceFactory $isProductSalableResultFactory;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\Stock $resourceModel,
        \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterfaceFactory $isProductSalableResultFactory
    ) {
        $this->resourceModel = $resourceModel;
        $this->isProductSalableResultFactory = $isProductSalableResultFactory;
    }

    public function execute(array $skus, array $backInStockItems): array
    {
        $result = [];

        $stockData = $this->resourceModel->getStockDataForSkus($skus);

        foreach ($backInStockItems as $sku => $stockInfo) {
            foreach ($stockInfo as $stockId => $itemInfo) {
                $salableQty = $this->getSalableQty($sku, $stockId, $stockData);

                $isSalable = $salableQty >= 0;
                $wasSalable = $isSalable && $this->checkPreviousSalableStatus($salableQty, $itemInfo);

                $result[$sku][$stockId] = $this->isProductSalableResultFactory->create(
                    [
                        'wasSalable' => $wasSalable,
                        'isSalable' => $isSalable,
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * Product is salable if stock quantity minus reservations minus minimum quantity
     * is equal or bigger than 0.
     * Important: reservations have negative sign in database, so we are "adding" them.
     */
    protected function getSalableQty($sku, $stockId, $stockData)
    {
        $stockQty = $stockData->getStockQtys()[$sku][$stockId] ?? 0;
        $reservationQty = $stockData->getReservations()[$sku][$stockId] ?? 0;
        $minimumQty = $stockData->getMinimumQtys()[$sku] ?? 0;
        $outOfStockThreshold = $stockData->getOutOfStockThreshold()[$sku] ?? 0;

        return $stockQty + $reservationQty - $minimumQty - $outOfStockThreshold;
    }

    /**
     * Product should be added to back in stock notification only if it was out of stock
     * and is in stock now. For this reason previous salable status needs to be checked.
     * First, old and new quantities are compared and later old stock status.
     */
    protected function checkPreviousSalableStatus($salableQty, $itemInfo)
    {
        $qtyDelta = $itemInfo['new_qty'] - $itemInfo['old_qty'];

        $wasSalable = $salableQty - $qtyDelta >= 0;

        if (!$wasSalable) {
            return false;
        }

        return $itemInfo['old_status'] == \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK;
    }
}
