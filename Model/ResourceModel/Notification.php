<?php
namespace MageSuite\BackInStock\Model\ResourceModel;

class Notification extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('back_in_stock_notification_queue', 'id');
    }
}