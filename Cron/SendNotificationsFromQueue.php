<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Cron;

class SendNotificationsFromQueue
{
    public function __construct(
        protected \MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
    ) {}

    public function execute(): void
    {
        if (!$this->configuration->isModuleEnabled()) {
            return;
        }

        $automaticRemoveSubscription = $this->configuration->isSubscriptionRemovedAfterNotification();
        $isHistoricalDataKept = $this->configuration->isHistoricalDataKept();

        $this->notificationQueueSender->send($automaticRemoveSubscription, $isHistoricalDataKept);
    }
}
