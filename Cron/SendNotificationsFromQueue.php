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
    private $configuration;

    public function __construct(
        \MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    )
    {
        $this->notificationQueueSender = $notificationQueueSender;
        $this->configuration = $configuration;
    }

    public function execute()
    {
        if (!$this->configuration->isModuleEnabled()) {
            return;
        }
        $this->notificationQueueSender->send();
    }
}