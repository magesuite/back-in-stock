<?php
namespace MageSuite\BackInStock\Controller\Adminhtml\Grid;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $pageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    public function execute()
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('MageSuite_BackInStock::back_in_stock');
        $resultPage->getConfig()->getTitle()->prepend((__('Back In Stock')));

        $resultPage->addBreadcrumb(__('Back In Stock'), __('Back In Stock'));

        return $resultPage;
    }

    protected function _isAllowed()
    {
        return true;
    }
}