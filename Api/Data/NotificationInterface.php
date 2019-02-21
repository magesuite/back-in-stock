<?php
namespace MageSuite\BackInStock\Api\Data;

interface NotificationInterface
{
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
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setCustomerEmail($customerEmail);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $storeId
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getProductId();

    /**
     * @param string $productId
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setProductId($productId);

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