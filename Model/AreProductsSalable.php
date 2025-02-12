<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class AreProductsSalable implements \MageSuite\BackInStock\Api\AreProductsSalableInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterfaceFactory $isProductSalableResultFactory,
        protected \Magento\InventoryIndexer\Indexer\SourceItem\GetSalableStatuses $getSalableStatuses
    ) {
    }

    public function execute(array $skus, array $backInStockItems): array
    {
        $result = [];

        $sourceItemIds = $this->getSourceItemsIds($backInStockItems);
        $salableStatusesAfter = $this->getSalableStatuses(array_values($sourceItemIds));

        foreach ($backInStockItems as $sku => $backInStockItem) {
            $salableStatusBefore = $backInStockItem['salable_status_before'];
            foreach ($salableStatusBefore as $stockId => $wasSalable) {
                $result[$sku][$stockId] = $this->isProductSalableResultFactory->create(
                    [
                        'wasSalable' => $wasSalable,
                        'isSalable' => $salableStatusesAfter[$sku][$stockId],
                    ]
                );
            }
        }

        return $result;
    }

    protected function getSourceItemsIds(array $backInStockItems): array
    {
        $sourceItemIds = [];

        foreach ($backInStockItems as $backInStockItem) {
            foreach ($backInStockItem['source_items'] as $sourceItem) {
                $sourceItemIds[$sourceItem['item_id']] = $sourceItem['item_id'];
            }
        }

        return $sourceItemIds;
    }

    protected function getSalableStatuses(array $sourceItemIds): array
    {
        return $this->getSalableStatuses->execute($sourceItemIds);
    }
}
