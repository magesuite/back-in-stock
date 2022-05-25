<?php

namespace MageSuite\BackInStock\Service;

class NotificationQueueSender
{
    const MANUAL_NOTIFICATION = 'manual_notification';
    const AUTOMATIC_NOTIFICATION = 'automatic_notification';

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \MageSuite\BackInStock\Api\NotificationRepositoryInterface
     */
    protected $notificationRepository;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var \MageSuite\BackInStock\Helper\Subscription
     */
    protected $subscriptionHelper;

    /**
     * @var array
     */
    protected $sendersByChannel;

    /**
     * @param \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository
     * @param \MageSuite\BackInStock\Api\NotificationRepositoryInterface $notificationRepository
     * @param \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
     * @param \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper
     * @param array $sendersByChannel
     */
    public function __construct(
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Api\NotificationRepositoryInterface $notificationRepository,
        \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory,
        \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        $sendersByChannel = []
    ) {
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->notificationRepository = $notificationRepository;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->subscriptionHelper = $subscriptionHelper;
        $this->sendersByChannel = $sendersByChannel;
    }

    public function send($automaticRemoveSubscription = false, $isHistoricalDataKept = true)
    {
        $notificationCollection = $this->notificationCollectionFactory->create();

        /** @var \MageSuite\BackInStock\Model\Notification $notification */
        foreach ($notificationCollection as $notification) {
            $subscription = $this->backInStockSubscriptionRepository->getById($notification->getSubscriptionId());

            if ($subscription->isCustomerUnsubscribed()
                || $subscription->isRemoved()
                || $this->subscriptionHelper->isSubscriptionRejected($subscription->isCustomerConfirmed(), $subscription->isCustomerUnsubscribed(), $subscription->getAddDate())) {
                $this->notificationRepository->delete($notification);
                continue;
            }

            $channel = $subscription->getNotificationChannel();

            if (!isset($this->sendersByChannel[$channel])) {
                continue;
            }

            $sendNotificationStatus = $this->sendersByChannel[$channel]->send($notification, $subscription);

            $subscription
                ->setSendCount($subscription->getSendCount() + 1)
                ->setSendDate(date("Y-m-d H:i:s"))
                ->setSendNotificationStatus($sendNotificationStatus);
            $this->backInStockSubscriptionRepository->save($subscription);

            if ($automaticRemoveSubscription) {
                $this->backInStockSubscriptionRepository->unsubscribe($subscription, $isHistoricalDataKept);
            }

            $this->notificationRepository->delete($notification);
        }

        return $this;
    }
}
