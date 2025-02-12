<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\Command;

class GetBackInStockItems
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\InventoryIndexer\Indexer\SourceItem\GetSalableStatuses $getSalableStatuses
    ) {
    }

    public function execute($sourceItems): array
    {
        if (empty($sourceItems)) {
            return [];
        }

        $inStockSourceItemsData = $this->getInStockSourceItemsData($sourceItems);

        if (empty($inStockSourceItemsData->getSkus())) {
            return [];
        }

        return $this->collectBackInStockItems($inStockSourceItemsData);
    }

    protected function getInStockSourceItemsData($sourceItems): \Magento\Framework\DataObject
    {
        $skus = [];
        $itemIds = [];
        $sourceItemsMap = [];

        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getStatus() != \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK) {
                continue;
            }

            $skus[] = $sourceItem->getSku();
            $itemIds[] = $sourceItem->getId();
            $sourceItemsMap[$sourceItem->getSku()][$sourceItem->getSourceCode()] = [
                'new_qty' => $sourceItem->getQuantity(),
                'new_status' => $sourceItem->getStatus(),
                'item_id' => $sourceItem->getId()
            ];
        }

        return new \Magento\Framework\DataObject([
            'skus' => $skus,
            'item_ids' => $itemIds,
            'source_items_map' => $sourceItemsMap
        ]);
    }

    protected function collectBackInStockItems($inStockSourceItemsData): array
    {
        //returns array [sku][stockId] => salable_status
        $salableStatusesBefore = $this->getSalableStatuses($inStockSourceItemsData->getItemIds());

        $currentSourceItems = $this->getCurrentSourceItems($inStockSourceItemsData->getSkus());

        $sourceItems = [];
        $result = [];

        foreach ($currentSourceItems as $currentSourceItem) {

            $updatedItem = $this->getUpdatedSourceItem($currentSourceItem, $inStockSourceItemsData);

            if (!$updatedItem) {
                continue;
            }

            $sourceItems[$currentSourceItem['sku']][$currentSourceItem['source_code']] = [
                'old_qty' => $currentSourceItem['quantity'],
                'new_qty' => $updatedItem['new_qty'],
                'old_status' => $currentSourceItem['status'],
                'new_status' => $updatedItem['new_status'],
                'item_id' => $updatedItem['item_id'],
            ];

            $result[$currentSourceItem['sku']] = [
                'source_items' => $sourceItems[$currentSourceItem['sku']],
                'salable_status_before' => $salableStatusesBefore[$currentSourceItem['sku']]
            ];

        }

        return $result;
    }

    protected function getCurrentSourceItems($skus)
    {
        $connection = $this->resourceConnection->getConnection();

        $query = $connection
            ->select()
            ->from(['source_item' => $this->resourceConnection->getTableName('inventory_source_item')])
            ->where('source_item.sku IN (?)', $skus);

        return $connection->fetchAll($query);
    }

    protected function getUpdatedSourceItem($currentSourceItem, $inStockSourceItemsData)
    {
        $updatedItem = $inStockSourceItemsData->getSourceItemsMap()[$currentSourceItem['sku']][$currentSourceItem['source_code']] ?? null;

        if (!$updatedItem) {
            return null;
        }

        if ((float)$currentSourceItem['quantity'] >= $updatedItem['new_qty'] &&
            $currentSourceItem['status'] == \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
        ) {
            return null;
        }

        return $updatedItem;
    }

    protected function getSalableStatuses(array $sourceItemIds): array
    {
        return $this->getSalableStatuses->execute($sourceItemIds);
    }
}
