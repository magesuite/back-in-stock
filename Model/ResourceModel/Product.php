<?php

namespace MageSuite\BackInStock\Model\ResourceModel;

class Product
{
    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function getSkuByProductId($productId)
    {
        $query = $this->connection
            ->select()
            ->from(['e' => $this->connection->getTableName('catalog_product_entity')], ['e.sku'])
            ->where('e.entity_id = ?', $productId);

        try {
            return $this->connection->fetchOne($query);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getTypeIdByProductId($productId)
    {
        $query = $this->connection
            ->select()
            ->from(['e' => $this->connection->getTableName('catalog_product_entity')], ['e.type_id'])
            ->where('e.entity_id = ?', $productId);

        try {
            return $this->connection->fetchOne($query);
        } catch (\Exception $e) {
            return null;
        }
    }
}
