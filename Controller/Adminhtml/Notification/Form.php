<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Form extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);

        $this->resultRawFactory = $resultRawFactory;
        $this->layoutFactory = $layoutFactory;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $layout = $this->layoutFactory->create();

        $block = $layout->createBlock(\MageSuite\BackInStock\Block\Adminhtml\Notification\Form::class)
            ->toHtml();

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($block);

        return $resultRaw;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
