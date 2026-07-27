<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\SourceItem;

class GetSkuListInStockBySkus
{
    public function __construct(
         protected \Magento\Framework\App\ResourceConnection $resourceConnection,
         protected \Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStockFactory $skuListInStockFactory
    ) {
    }

    public function execute(array $sourceItemSkus): array
    {
        $connection = $this->resourceConnection->getConnection();
        $sourceStockLinkTable = $this->resourceConnection->getTableName(
            \Magento\Inventory\Model\ResourceModel\StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK
        );
        $sourceItemTable = $this->resourceConnection->getTableName(
            \Magento\Inventory\Model\ResourceModel\SourceItem::TABLE_NAME_SOURCE_ITEM
        );
        $items = [];

        $select = $connection
            ->select()
            ->from(
                ['source_item' => $sourceItemTable],
                [\Magento\InventoryApi\Api\Data\SourceItemInterface::SKU => 'source_item.' . \Magento\InventoryApi\Api\Data\SourceItemInterface::SKU]
            )->joinInner(
                ['stock_source_link' => $sourceStockLinkTable],
                sprintf(
                    'source_item.%s = stock_source_link.%s',
                    \Magento\InventoryApi\Api\Data\SourceItemInterface::SOURCE_CODE,
                    \Magento\Inventory\Model\StockSourceLink::SOURCE_CODE
                ),
                [\Magento\Inventory\Model\StockSourceLink::STOCK_ID]
            )->where(
                'source_item.sku IN (?)',
                $sourceItemSkus
            );

        $dbStatement = $connection->query($select);
        while ($item = $dbStatement->fetch()) {
            $items[$item[\Magento\Inventory\Model\StockSourceLink::STOCK_ID]][$item[\Magento\InventoryApi\Api\Data\SourceItemInterface::SKU]] = $item[\Magento\InventoryApi\Api\Data\SourceItemInterface::SKU];
        }

        return $this->getStockIdToSkuList($items);
    }

    private function getStockIdToSkuList(array $items): array
    {
        $skuListInStockList = [];
        foreach ($items as $stockId => $skuList) {
            /** @var \Magento\InventoryIndexer\Indexer\SourceItem\SkuListInStock $skuListInStock */
            $skuListInStock = $this->skuListInStockFactory->create([
                'stockId' => (int) $stockId,
                'skuList' => $skuList
            ]);
            $skuListInStock->setStockId((int) $stockId);
            $skuListInStock->setSkuList($skuList);
            $skuListInStockList[] = $skuListInStock;
        }
        return $skuListInStockList;
    }
}
