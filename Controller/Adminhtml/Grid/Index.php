<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Grid;

class Index extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('MageSuite_BackInStock::back_in_stock');
        $resultPage->getConfig()->getTitle()->prepend((__('Back In Stock')));
        $resultPage->addBreadcrumb(__('Back In Stock'), __('Back In Stock'));

        return $resultPage;
    }
}
