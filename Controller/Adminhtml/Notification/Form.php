<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Form extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_BackInStock::config_backinstock';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Framework\View\LayoutFactory $layoutFactory,
        protected \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute(): \Magento\Framework\Controller\Result\Raw
    {
        $layout = $this->layoutFactory->create();
        $block = $layout->createBlock(\MageSuite\BackInStock\Block\Adminhtml\Notification\Form::class)
            ->toHtml();
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($block);

        return $resultRaw;
    }
}
