<?php
namespace MageSuite\BackInStock\Service;

class NotificationQueueCreator
{
    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * @var \MageSuite\BackInStock\Model\NotificationFactory
     */
    protected $notificationFactory;

    /**
     * @var \MageSuite\BackInStock\Api\NotificationRepositoryInterface
     */
    protected $notificationRepository;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        \MageSuite\BackInStock\Model\NotificationFactory $notificationFactory,
        \MageSuite\BackInStock\Api\NotificationRepositoryInterface $notificationRepository
    ) {
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->notificationFactory = $notificationFactory;
        $this->notificationRepository = $notificationRepository;
    }

    public function addNotificationsToQueue($productId, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID, $notificationType = '', $message = '') //phpcs:ignore
    {
        $subscriptionCollection = $this->getSubscriptions($productId, $storeId);

        foreach ($subscriptionCollection as $subscription) {
            $notification = $this->notificationFactory->create();

            $notification
                ->setSubscriptionId($subscription->getId())
                ->setNotificationType($notificationType)
                ->setMessage($message);

            $this->notificationRepository->save($notification);
        }
    }

    public function getSubscriptions($productId, $storeId)
    {
        $collection = $this->subscriptionCollectionFactory->create();

        $collection
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->addFieldToFilter('store_id', ['eq' => $storeId])
            ->addFieldToFilter('customer_confirmed', ['eq' => 1])
            ->addFieldToFilter('customer_unsubscribed', ['eq' => 0])
            ->addFieldToFilter('is_removed', ['eq' => 0]);

        return $collection;
    }
}
