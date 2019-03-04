<?php
namespace MageSuite\BackInStock\Controller\Adminhtml\Customer;

class Subscriptions extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}