<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Product;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        protected \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        parent::__construct($context, $productBuilder);
    }

    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
