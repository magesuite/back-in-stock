<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Product;

class Remove extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \MageSuite\BackInStock\Service\Unsubscriber
     */
    protected $unsubscriber;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\BackInStock\Service\Unsubscriber $unsubscriber
    ) {
        parent::__construct($context);

        $this->unsubscriber = $unsubscriber;
    }

    public function execute()
    {
        $subscriptionId = (int)$this->getRequest()->getParam('id');

        $this->unsubscriber->execute([$subscriptionId]);
        $this->messageManager->addSuccessMessage(__('Email was removed from back-in-stock notification.'));

        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return $resultRedirect;
    }
}
