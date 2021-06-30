<?php
namespace MageSuite\BackInStock\Api\Data;

interface BackInStockSubscriptionInterface
{
    const ID = 'id';
    const CUSTOMER_ID = 'customer_id';
    const CUSTOMER_EMAIL = 'customer_email';
    const PRODUCT_ID = 'product_id';
    const PARENT_PRODUCT_ID = 'parent_product_id';
    const STORE_ID = 'store_id';
    const ADD_DATE = 'add_date';
    const SEND_DATE = 'send_date';
    const SEND_COUNT = 'send_count';
    const WAS_NOTIFICATION_SENT = 'was_notification_sent';
    const CUSTOMER_CONFIRMED = 'customer_confirmed';
    const TOKEN = 'token';
    const NOTIFICATION_CHANNEL = 'notification_channel';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setId($id);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $customerId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerId($customerId);

    /**
     * @return string
     */
    public function getCustomerEmail();

    /**
     * @param string $customerEmail
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerEmail($customerEmail);

    /**
     * @return string
     */
    public function getProductId();

    /**
     * @param string $productId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setProductId($productId);

    /**
     * @return string
     */
    public function getParentProductId();

    /**
     * @param string $parentProductId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setParentProductId($parentProductId);

    /**
     * @return string
     */
    public function getStoreId();

    /**
     * @param string $storeId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setStoreId($storeId);

    /**
     * @return string
     */
    public function getAddDate();

    /**
     * @param string $addDate
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setAddDate($addDate);

    /**
     * @return int
     */
    public function getSendDate();

    /**
     * @param int $sendDate
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendDate($sendDate);

    /**
     * @return int
     */
    public function getSendCount();

    /**
     * @param int $sendCount
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendCount($sendCount);

    /**
     * @return int
     */
    public function getWasNotificationSent();

    /**
     * @param int $wasNotificationSent
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setWasNotificationSent($wasNotificationSent);

    /**
     * @return int
     */
    public function isCustomerConfirmed();

    /**
     * @param bool $confirmed
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerConfirmed(bool $confirmed);

    /**
     * @return bool
     */
    public function isCustomerUnsubscribed(): bool;

    /**
     * @param bool $unsubscribed
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerUnsubscribed(bool $unsubscribed);

    /**
     * @return int
     */
    public function getToken();

    /**
     * @param int $token
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setToken($token);

    /**
     * @return string
     */
    public function getNotificationChannel();

    /**
     * @param string $channel
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setNotificationChannel($channel);
}
