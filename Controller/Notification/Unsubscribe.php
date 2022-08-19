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

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        parent::__construct($context);

        $this->pageFactory = $pageFactory;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setPath($url);

        try {
            $subscription = $this->backInStockSubscriptionRepository->getById($params['id']);
            $isHistoricalDataKept = $this->configuration->isHistoricalDataKept();

            if (!$this->validateToken($subscription, $params)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Something went wrong while processing unsubscribe. Please contact store owner.'));
            }

            if ($subscription->isCustomerUnsubscribed()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('You have been already unsubscribed from back in stock notification.'));
            }

            if ($subscription->isRemoved()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This subscription does not exist.'));
            }

            $this->backInStockSubscriptionRepository->unsubscribe($subscription, $isHistoricalDataKept);

            $this->messageManager->addSuccessMessage(__('Correctly unsubscribed from back in stock notification.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
            return $resultRedirect;
        }
        return $resultRedirect;
    }

    public function validateToken($subscription, $params)
    {
        if ($subscription->getNotificationChannel() != self::NOTIFICATION_CHANNEL_EMAIL) {
            return true;
        }

        $token = $params['token'] ?? null;

        if (!$token || $subscription->getToken() !== $token) {
            return false;
        }

        return true;
    }
}
