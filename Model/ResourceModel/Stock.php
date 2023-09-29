<?php

namespace MageSuite\BackInStock\Model\ResourceModel;

class Stock
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * @var \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface
     */
    protected $stockIndexTableNameResolver;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     */
    protected $stockConfiguration;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface $stockIndexTableNameResolver,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
    ) {
        $this->connection = $resourceConnection->getConnection();
        $this->stockIndexTableNameResolver = $stockIndexTableNameResolver;
        $this->stockConfiguration = $stockConfiguration;
    }

    public function getStockIdSourceCodeMap()
    {
        $query = $this->connection
            ->select()
            ->from(['link_table' => $this->connection->getTableName('inventory_source_stock_link')], ['stock_id', 'source_code']);

        $items = $this->connection->fetchAll($query);

        $map = [];

        foreach ($items as $item) {
            $map[$item['stock_id']][] = $item['source_code'];
        }

        return $map;
    }

    public function getStockDataForSkus($skus)
    {
        return new \Magento\Framework\DataObject([
            'stock_qtys' => $this->getStockQtys($skus),
            'reservations' => $this->getReservations($skus),
            'minimum_qtys' => $this->getMinimumQtys($skus),
            'out_of_stock_threshold' => $this->getOutOfStockThreshold($skus),
        ]);
    }

    protected function getStockQtys($skus)
    {
        $stockTables = $this->getStockTables();
        $stockIds = $this->getStockIds($stockTables);

        $firstStockTable = array_shift($stockTables);
        $firstStockTableName = $this->connection->getTableName($firstStockTable['stock_table']);

        $parentSelect = $this->connection
            ->select()
            ->from(
                $firstStockTableName,
                [
                    'sku AS ' . $firstStockTable['stock_id'] . '.sku',
                    'quantity AS ' . $firstStockTable['stock_id'] . '.quantity'
                ]
            );

        foreach ($stockTables as $stockTable) {
            $stockTableName = $this->connection->getTableName($stockTable['stock_table']);

            $parentSelect->joinLeft(
                $stockTableName,
                sprintf('%s.sku = %s.sku', $firstStockTableName, $stockTableName),
                [
                    'sku AS ' . $stockTable['stock_id'] . '.sku',
                    'quantity AS ' . $stockTable['stock_id'] . '.quantity'
                ]
            );
        }

        $parentSelect
            ->where($firstStockTableName . '.sku IN (?)', $skus)
            ->group($firstStockTableName . '.sku');

        $items = $this->connection->fetchAll($parentSelect);

        $stockQtys = [];

        foreach ($items as $item) {
            foreach ($stockIds as $stockId) {
                $sku = $item[$stockId . '.sku'] ?? null;
                $itemStockQty = $item[$stockId . '.quantity'] ?? null;

                if ($sku == null || $itemStockQty == null) {
                    continue;
                }

                $stockQtys[$sku][$stockId] = $itemStockQty;
            }

        }

        return $stockQtys;
    }

    protected function getReservations($skus)
    {
        $query = $this->connection
            ->select()
            ->from(['reservation' => $this->connection->getTableName('inventory_reservation')], ['sku', 'stock_id', 'SUM(quantity) AS reservation'])
            ->where('reservation.sku IN (?)', $skus)
            ->group(['sku', 'stock_id']);

        $items = $this->connection->fetchAll($query);

        $reservations = [];

        foreach ($items as $item) {
            $reservations[$item['sku']][$item['stock_id']] = $item['reservation'];
        }

        return $reservations;
    }

    protected function getMinimumQtys($skus)
    {
        $query = $this->connection
            ->select()
            ->from(['stock_item' => $this->connection->getTableName('cataloginventory_stock_item')], ['e.sku', 'stock_item.min_sale_qty'])
            ->joinLeft(['e' => $this->connection->getTableName('catalog_product_entity')], 'stock_item.product_id = e.entity_id')
            ->where('e.sku IN (?)', $skus);

        return $this->connection->fetchPairs($query);
    }

    protected function getOutOfStockThreshold($skus)
    {
        $configStockThreshold = $this->stockConfiguration->getMinQty();
        $query = $this->connection
            ->select()
            ->from(
                ['stock_item' => $this->connection->getTableName('cataloginventory_stock_item')],
                [
                    'e.sku',
                    'stock_threshold' => new \Zend_Db_Expr(sprintf("IF (`stock_item`.`use_config_min_qty` = 1, %s, `stock_item`.`min_qty`)", $configStockThreshold)),
                ]
            )->joinLeft(
                ['e' => $this->connection->getTableName('catalog_product_entity')],
                'stock_item.product_id = e.entity_id',
                []
            )->where('e.sku IN (?)', $skus);

        return $this->connection->fetchPairs($query);
    }

    protected function getStockTables()
    {
        $query = $this->connection
            ->select()
            ->from(['stock' => $this->connection->getTableName('inventory_stock')], ['stock_id']);

        $stockIds = $this->connection->fetchCol($query);

        $stockTables = [];

        foreach ($stockIds as $stockId) {
            $stockTables[] = [
                'stock_id' => $stockId,
                'stock_table' => $this->stockIndexTableNameResolver->execute($stockId)
            ];
        }

        return $stockTables;
    }

    protected function getStockIds($stockTables)
    {
        $stockIds = array_column($stockTables, 'stock_id');

        return array_unique($stockIds);
    }
}
