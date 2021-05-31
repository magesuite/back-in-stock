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
    )
    {
        $this->pageFactory = $pageFactory;
        parent::__construct($context);

        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $resultRedirect = $this->resultRedirectFactory->create();
        $url = $this->_redirect->getRefererUrl();
        $resultRedirect->setPath($url);

        try {
            $subscription = $this->backInStockSubscriptionRepository->getById($params['id']);

            if(!$this->validateToken($subscription, $params)){
                throw new \Exception(__('Something went wrong while processing unsubscribe. Please contact store owner.'));
            }

            $this->backInStockSubscriptionRepository->delete($subscription);

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
