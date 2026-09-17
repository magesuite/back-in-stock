<?php

namespace MageSuite\BackInStock\Controller\Notification;

class Index extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        if (!$this->customerSession->getCustomerId()) {
            $this->messageManager->addNoticeMessage(__('Your session expired, please login again.'));

            return $this->resultRedirectFactory->create()->setPath('customer/account/login');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__('My Back In Stock Notifications'));

        return $resultPage;
    }
}
