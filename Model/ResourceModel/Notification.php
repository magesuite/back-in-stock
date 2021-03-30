<?php
namespace MageSuite\BackInStock\Model\ResourceModel;

class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    const TABLE_NAME = 'back_in_stock_notification_queue';

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

    public function insertMultipleNotifications($notifications)
    {
        $tableName = $this->connection->getTableName($this->getMainTable());

        return $this->connection->insertMultiple($tableName, $notifications);
    }
}
