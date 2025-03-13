<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class AreProductsSalable implements \MageSuite\BackInStock\Api\AreProductsSalableInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterfaceFactory $isProductSalableResultFactory,
        protected \MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses $getSalableStatuses
    ) {
    }

    public function execute(array $skus, array $backInStockItems): array
    {
        $result = [];
        $salableStatusesAfter = $this->getSalableStatuses($skus);

        foreach ($backInStockItems as $sku => $backInStockItem) {
            $salableStatusBefore = $backInStockItem['salable_status_before'];
            foreach ($salableStatusBefore as $stockId => $wasSalable) {
                $result[$sku][$stockId] = $this->isProductSalableResultFactory->create(
                    [
                        'wasSalable' => $wasSalable,
                        'isSalable' => $salableStatusesAfter[$sku][$stockId] ?? false,
                    ]
                );
            }
        }

        return $result;
    }

    protected function getSalableStatuses(array $skus): array
    {
        return $this->getSalableStatuses->execute($skus);
    }
}
