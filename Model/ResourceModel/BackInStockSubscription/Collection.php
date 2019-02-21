<?php
namespace MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('MageSuite\BackInStock\Model\BackInStockSubscription', 'MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription');
    }
}