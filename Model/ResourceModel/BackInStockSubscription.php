<?php
namespace MageSuite\BackInStock\Model\ResourceModel;

class BackInStockSubscription extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'back_in_stock_subscription_entity';

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct($context);

        $this->connection = $resource->getConnection();
    }

    public function _construct()
    {
        $this->_init(self::TABLE_NAME, 'id');
    }

    public function getSubscriptionsBySkus($skus)
    {
        $tableName = $this->connection->getTableName($this->getMainTable());

        $query = $this->connection
            ->select()
            ->from(['s' => $tableName], 's.*')
            ->joinLeft(['e' => $this->connection->getTableName('catalog_product_entity')], 's.product_id = e.entity_id', 'e.sku')
            ->where('e.sku IN (?)', $skus)
            ->where('s.customer_confirmed = ?', 1)
            ->where('s.is_removed = ?', 0);

        return $this->connection->fetchAll($query);
    }
}
