<?php
namespace MageSuite\BackInStock\Controller\Adminhtml\Customer;

class Subscriptions extends \Magento\Customer\Controller\Adminhtml\Index
{
    public function execute()
    {
        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}