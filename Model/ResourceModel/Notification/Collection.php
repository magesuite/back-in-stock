<?php

namespace MageSuite\BackInStock\Model\ResourceModel\Notification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\Notification::class, \MageSuite\BackInStock\Model\ResourceModel\Notification::class);
    }
}
