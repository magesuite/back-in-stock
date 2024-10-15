<?php

namespace MageSuite\BackInStock\Controller\Notification;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{
    const NOTIFICATION_CHANNEL_EMAIL = 'email';

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $pageFactory;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository
    ) {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
    }

    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setPath($url);

        try {
            $subscritpionId = (int)$this->_request->getParam('id');
            $subscription = $this->backInStockSubscriptionRepository->getById($subscritpionId);

            if (!$this->validateSubscription($subscription)) {
                return $resultRedirect;
            }

            $subscription->setCustomerUnsubscribed(true);
            $this->backInStockSubscriptionRepository->save($subscription);

            $this->messageManager->addSuccessMessage(__('Correctly unsubscribed from back in stock notification.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
            return $resultRedirect;
        }
        return $resultRedirect;
    }

    protected function validateSubscription(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): bool
    {
        try {
            if (!$this->validateToken($subscription)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Something went wrong while processing unsubscribe. Please contact store owner.'));
            }

            if ($subscription->isCustomerUnsubscribed()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('You have been already unsubscribed from back in stock notification.'));
            }

            if ($subscription->isRemoved()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This subscription does not exist.'));
            }

            return true;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return false;
        }
    }

    public function validateToken(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): bool
    {
        if ($subscription->getNotificationChannel() != self::NOTIFICATION_CHANNEL_EMAIL) {
            return true;
        }

        $token = $this->_request->getParam('token');

        if (!$token || $subscription->getToken() !== $token) {
            return false;
        }

        return true;
    }
}
