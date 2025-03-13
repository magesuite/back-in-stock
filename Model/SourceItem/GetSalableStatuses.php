<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\SourceItem;

class GetSalableStatuses
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\SourceItem\GetSkuListInStockBySkus $getSkuListInStock,
        protected \Magento\InventorySalesApi\Api\AreProductsSalableInterface $areProductsSalable
    ) {
    }

    public function execute(array $sourceItemSkus) : array
    {
        $result = [];
        $skuListInStockList = $this->getSkuListInStock->execute($sourceItemSkus);

        foreach ($skuListInStockList as $skuListInStock) {
            $stockId = $skuListInStock->getStockId();
            $skuList = $skuListInStock->getSkuList();
            $salableStatusList = $this->areProductsSalable->execute($skuList, $stockId);

            foreach ($salableStatusList as $salableStatusItem) {
                $result[$salableStatusItem->getSku()][$stockId] = $salableStatusItem->isSalable();
            }
        }

        return $result;
    }
}
