<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\Command;

class GetBackInStockItems
{
    public function __construct(
        protected \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses $getSalableStatuses,
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription $subscriptionResourceModel,
        protected \MageSuite\BackInStock\Model\Command\GetDisabledProductSkus $getDisabledProductSkus,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
    ) {}

    public function execute($sourceItems): array
    {
        if (empty($sourceItems)) {
            return [];
        }

        $sourceItems = $this->filterSourceItemsWithoutValidSubscriptions($sourceItems);
        $sourceItems = $this->filterDisabledProducts($sourceItems);
        $inStockSourceItemsData = $this->getInStockSourceItemsData($sourceItems);

        if (empty($inStockSourceItemsData->getSkus())) {
            return [];
        }

        return $this->collectBackInStockItems($inStockSourceItemsData);
    }

    protected function getInStockSourceItemsData($sourceItems): \Magento\Framework\DataObject
    {
        $skus = [];
        $sourceItemsMap = [];

        foreach ($sourceItems as $sourceItem) {
            if ($sourceItem->getStatus() != \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK) {
                continue;
            }

            $skus[$sourceItem->getSku()] = $sourceItem->getSku();

            $sourceItemsMap[$sourceItem->getSku()][$sourceItem->getSourceCode()] = [
                'new_qty' => $sourceItem->getQuantity(),
                'new_status' => $sourceItem->getStatus()
            ];
        }

        return new \Magento\Framework\DataObject([
            'skus' => array_values($skus),
            'source_items_map' => $sourceItemsMap
        ]);
    }

    protected function collectBackInStockItems(\Magento\Framework\DataObject $inStockSourceItemsData): array
    {
        //returns array [sku][stockId] => salable_status
        $salableStatusesBefore = $this->getSalableStatuses($inStockSourceItemsData->getSkus());

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
                'new_status' => $updatedItem['new_status']
            ];

            $result[$currentSourceItem['sku']] = [
                'source_items' => $sourceItems[$currentSourceItem['sku']],
                'salable_status_before' => $salableStatusesBefore[$currentSourceItem['sku']] ?? []
            ];
        }

        return $result;
    }

    protected function getCurrentSourceItems(array $skus): array
    {
        $connection = $this->resourceConnection->getConnection();

        $query = $connection
            ->select()
            ->from(['source_item' => $this->resourceConnection->getTableName('inventory_source_item')])
            ->where('source_item.sku IN (?)', $skus);

        return $connection->fetchAll($query);
    }

    protected function getUpdatedSourceItem(array $currentSourceItem, \Magento\Framework\DataObject $inStockSourceItemsData)
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

    protected function getSalableStatuses(array $sourceItemSkus): array
    {
        return $this->getSalableStatuses->execute($sourceItemSkus);
    }

    protected function filterSourceItemsWithoutValidSubscriptions(array $sourceItems): array
    {
        $skus = array_unique(array_map(fn($sourceItem) => $sourceItem->getSku(), $sourceItems));
        $subscriptions = $this->subscriptionResourceModel->getSubscriptionsBySkus($skus);
        $subscribedSkus = array_unique(array_column($subscriptions, 'sku'));

        return array_filter($sourceItems, fn($sourceItem) => in_array($sourceItem->getSku(), $subscribedSkus));
    }

    protected function filterDisabledProducts(array $sourceItems): array
    {
        $skus = array_unique(array_map(fn($sourceItem) => $sourceItem->getSku(), $sourceItems));
        $disabledSkus = $this->getDisabledProductSkus->execute($skus, (int)$this->storeManager->getStore()->getId());

        return array_filter($sourceItems, fn($sourceItem) => !in_array($sourceItem->getSku(), $disabledSkus));
    }
}
