<?php
namespace MageSuite\BackInStock\Controller\Notification;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{
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
            $subscription = $this->backInStockSubscriptionRepository->getById($params['notification_id']);

            if(!$this->validateToken($subscription->getToken(), $params['token'])){
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

    public function validateToken($subscriptionToken, $postParamToken)
    {
        if($subscriptionToken !== $postParamToken){
            return false;
        }
        return true;
    }
}