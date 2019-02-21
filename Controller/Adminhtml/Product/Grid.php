<?php
namespace MageSuite\BackInStock\Controller\Adminhtml\Product;

class Grid extends \Magento\Catalog\Controller\Adminhtml\Product
{
    protected $resultLayoutFactory;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder,
        \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
    ) {
        $this->resultLayoutFactory = $resultLayoutFactory;
        parent::__construct($context, $productBuilder);
    }

    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
