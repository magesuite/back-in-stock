<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Grid;

class MassRemove extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
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
        $postData = $this->_request->getPost();
        $subscriptionIds = $postData['selected'] ?? [];

        $this->unsubscriber->execute($subscriptionIds);

        $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been removed.', count($subscriptionIds)));
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        return $resultRedirect->setPath('*/*/');
    }
}
