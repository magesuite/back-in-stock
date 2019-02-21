<?php
namespace MageSuite\BackInStock\Model\ResourceModel;

class BackInStockSubscription extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    public function _construct()
    {
        $this->_init('back_in_stock_subscription_entity', 'id');
    }
}