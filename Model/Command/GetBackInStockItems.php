<?php

namespace MageSuite\BackInStock\Model\Command;

class GetBackInStockItems
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute($sourceItems)
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

    protected function getInStockSourceItemsData($sourceItems)
    {
        $skus = [];
        $sourceItemsMap = [];

        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getStatus() != \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK) {
                continue;
            }

            if (!(float)$sourceItem->getQuantity()) {
                continue;
            }

            $skus[] = $sourceItem->getSku();
            $sourceItemsMap[$sourceItem->getSku()][$sourceItem->getSourceCode()] = [
                'new_qty' => $sourceItem->getQuantity()
            ];
        }

        return new \Magento\Framework\DataObject([
            'skus' => $skus,
            'source_items_map' => $sourceItemsMap
        ]);
    }

    protected function collectBackInStockItems($inStockSourceItemsData)
    {
        $currentSourceItems = $this->getCurrentSourceItems($inStockSourceItemsData->getSkus());

        $backInStockItems = [];

        foreach ($currentSourceItems as $currentSourceItem) {

            $updatedItem = $this->getUpdatedSourceItem($currentSourceItem, $inStockSourceItemsData);

            if (!$updatedItem) {
                continue;
            }

            $backInStockItems[$currentSourceItem['sku']][$currentSourceItem['source_code']] = [
                'old_qty' => $currentSourceItem['quantity'],
                'new_qty' => $updatedItem['new_qty'],
                'old_status' => $currentSourceItem['status']
            ];
        }

        return $backInStockItems;
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
}
