<?php
namespace MageSuite\BackInStock\Api\Data;

interface BackInStockSubscriptionInterface
{
    public const ID = 'id';
    public const CUSTOMER_ID = 'customer_id';
    public const CUSTOMER_EMAIL = 'customer_email';
    public const PRODUCT_ID = 'product_id';
    public const PARENT_PRODUCT_ID = 'parent_product_id';
    public const STORE_ID = 'store_id';
    public const ADD_DATE = 'add_date';
    public const SEND_DATE = 'send_date';
    public const SEND_COUNT = 'send_count';
    public const SEND_NOTIFICATION_STATUS = 'send_notification_status';
    public const CUSTOMER_CONFIRMED = 'customer_confirmed';
    public const CUSTOMER_UNSUBSCRIBED = 'customer_unsubscribed';
    public const TOKEN = 'token';
    public const NOTIFICATION_CHANNEL = 'notification_channel';
    public const IS_REMOVED = 'is_removed';

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
     * @return string
     */
    public function getSendNotificationStatus();

    /**
     * @param string $sendNotificationStatus
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendNotificationStatus($sendNotificationStatus);

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

    /**
     * @return bool
     */
    public function isRemoved(): bool;

    /**
     * @param bool $isRemoved
     */
    public function setIsRemoved(bool $isRemoved);
}
