<?php

namespace MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\BackInStockSubscription::class, \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription::class);
    }
}
