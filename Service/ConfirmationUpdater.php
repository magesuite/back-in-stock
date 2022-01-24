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
    ) {
        $this->messageManager = $messageManager;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
    }

    public function update($params)
    {
        try {
            if (!isset($params['id']) || !isset($params['token'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Missing required arguments in request URL.'));
            }

            $subscription = $this->backInStockSubscriptionRepository->getById($params['id']);

            if (!$this->validateToken($subscription->getToken(), $params['token'])) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Something went wrong while confirming your subscription. Please contact store owner.'));
            }

            if ($subscription->isCustomerUnsubscribed()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('You have been unsubscribed from back in stock notification.'));
            }

            if ($subscription->isCustomerConfirmed()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This subscription request has been already confirmed.'));
            }

            if ($subscription->isRemoved()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('This subscription does not exist.'));
            }

            if ($subscription->isConfirmationDeadlinePassed()) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Time for subscription confirmation passed'));
            }

            $subscription->setCustomerConfirmed(true);

            $this->backInStockSubscriptionRepository->save($subscription);

            $this->messageManager->addSuccessMessage(__('Back in stock subscription has been confirmed.'));
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e);
        }
    }

    public function validateToken($subscriptionToken, $postParamToken)
    {
        if ($subscriptionToken !== $postParamToken) {
            return false;
        }
        return true;
    }
}
