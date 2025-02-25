<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service;

class NotificationQueueSender
{
    const MANUAL_NOTIFICATION = 'manual_notification';
    const AUTOMATIC_NOTIFICATION = 'automatic_notification';

    public function __construct(
        protected \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        protected \MageSuite\BackInStock\Api\NotificationRepositoryInterface $notificationRepository,
        protected \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        protected \MageSuite\BackInStock\Model\LimitationValidator $limitsValidator,
        protected array $sendersByChannel = []
    ) {
    }

    public function send($automaticRemoveSubscription = false, $isHistoricalDataKept = true): void
    {
        $notificationCollection = $this->notificationCollectionFactory->create();

        /** @var \MageSuite\BackInStock\Model\Notification $notification */
        foreach ($notificationCollection as $notification) {
            $subscriptionId = $notification->getSubscriptionId();
            $subscription = $this->backInStockSubscriptionRepository->getById($subscriptionId);

            if (!$this->validate($notification, $subscription)) {
                continue;
            }

            $channel = $subscription->getNotificationChannel();
            $sendNotificationStatus = $this->sendersByChannel[$channel]->send($notification, $subscription);

            $subscription
                ->setSendCount($subscription->getSendCount() + 1)
                ->setSendCountDaily($subscription->getSendCountDaily() + 1)
                ->setSendDate(date("Y-m-d H:i:s"))
                ->setSendNotificationStatus($sendNotificationStatus);
            $this->backInStockSubscriptionRepository->save($subscription);

            if ($automaticRemoveSubscription) {
                $this->backInStockSubscriptionRepository->unsubscribe($subscription, $isHistoricalDataKept);
            }

            $this->notificationRepository->delete($notification);
        }
    }

    protected function validate($notification, $subscription): bool
    {
        if ($subscription->isCustomerUnsubscribed()
            || $subscription->isRemoved()
            || $this->subscriptionHelper->isSubscriptionRejected($subscription->isCustomerConfirmed(), $subscription->isCustomerUnsubscribed(), $subscription->getAddDate())
        ) {
            $this->notificationRepository->delete($notification);
            return false;
        }

        if (!isset($this->sendersByChannel[$subscription->getNotificationChannel()])) {
            return false;
        }

        if (!$this->limitsValidator->isMinTimeValid($subscription) || $this->limitsValidator->isDailyLimitExceeded($subscription)) {
            $this->notificationRepository->delete($notification);
            return false;
        }

        return true;
    }
}
