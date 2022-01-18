<?php
namespace MageSuite\BackInStock\Cron;

class SendNotificationsFromQueue
{
    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueSender
     */
    protected $notificationQueueSender;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        $this->notificationQueueSender = $notificationQueueSender;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        if (!$this->configuration->isModuleEnabled()) {
            return;
        }

        $automaticRemoveSubscription = $this->configuration->isRemoveSubscriptionAfterSendNotification();

        $this->notificationQueueSender->send($automaticRemoveSubscription);
    }
}
