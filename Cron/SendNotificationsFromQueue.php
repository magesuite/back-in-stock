<?php

namespace MageSuite\BackInStock\Cron;

class SendNotificationsFromQueue
{
    public function __construct(
        protected \MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
    ) {}

    public function execute()
    {
        if (!$this->configuration->isModuleEnabled()) {
            return;
        }

        $automaticRemoveSubscription = $this->configuration->isRemoveSubscriptionAfterSendNotification();
        $isHistoricalDataKept = $this->configuration->isHistoricalDataKept();

        $this->notificationQueueSender->send($automaticRemoveSubscription, $isHistoricalDataKept);
    }
}
