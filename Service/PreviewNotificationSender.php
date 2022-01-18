<?php
namespace MageSuite\BackInStock\Service;

class PreviewNotificationSender
{
    /**
     * @var EmailSender
     */
    protected $emailSender;

    public function __construct(\MageSuite\BackInStock\Service\EmailSender $emailSender)
    {
        $this->emailSender = $emailSender;
    }

    public function sendPreview($email, $storeId, $message)
    {
        $this->emailSender->sendMail($email, ['notification_message' => $message], \MageSuite\BackInStock\Api\Data\NotificationInterface::MANUAL_NOTIFICATION_TEMPLATE, $storeId);
    }
}
