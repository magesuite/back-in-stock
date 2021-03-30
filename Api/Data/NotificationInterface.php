<?php
namespace MageSuite\BackInStock\Api\Data;

interface NotificationInterface
{
    const SUBSCRIPTION_ID = 'subscription_id';
    const NOTIFICATION_TYPE = 'notification_type';
    const MESSAGE = 'message';
    const AUTOMATIC_NOTIFICATION_TEMPLATE = 'back_in_stock/email_configuration/automatic_notification_email_template';
    const MANUAL_NOTIFICATION_TEMPLATE = 'back_in_stock/email_configuration/manual_notification_email_template';
    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getSubscriptionId();

    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setSubscriptionId($id);

    /**
     * @return string
     */
    public function getNotificationType();

    /**
     * @param string $notificationType
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setNotificationType($notificationType);

    /**
     * @return int
     */
    public function getMessage();

    /**
     * @param int $message
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setMessage($message);
}
