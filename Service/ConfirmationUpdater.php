<?php
namespace MageSuite\BackInStock\Service;

class ConfirmationUpdater
{
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;


    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository
    )
    {
        $this->messageManager = $messageManager;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
    }

    public function update($params)
    {
        try {
            if(!isset($params['id']) || !isset($params['token'])) {
                throw new \Exception(__('Missing required arguments in request URL.'));
            }

            $subscription = $this->backInStockSubscriptionRepository->getById($params['id']);

            if(!$this->validateToken($subscription->getToken(), $params['token'])){
                throw new \Exception(__('Something went wrong while confirming your subscription. Please contact store owner.'));
            }

            if($subscription->getCustomerConfirmed()){
                throw new \Exception(__('This subscription request has been already confirmed.'));
            }

            $subscription->setCustomerConfirmed(1);

            $this->backInStockSubscriptionRepository->save($subscription);

            $this->messageManager->addSuccessMessage(__('Back in stock subscription has been confirmed.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
    }

    public function validateToken($subscriptionToken, $postParamToken)
    {
        if($subscriptionToken !== $postParamToken){
            return false;
        }
        return true;
    }
}
