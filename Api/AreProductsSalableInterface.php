<?php

namespace MageSuite\BackInStock\Api;

interface AreProductsSalableInterface
{
    /**
     * Get products salable status for given SKUs and given Stock.
     *
     * @param string[] $skus
     * @param array $backInStockItems
     * @return \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterface[]
     */
    public function execute(array $skus, array $backInStockItems): array;
}
