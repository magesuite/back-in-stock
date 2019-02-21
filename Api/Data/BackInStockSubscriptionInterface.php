<?php
namespace MageSuite\BackInStock\Api\Data;

interface BackInStockSubscriptionInterface
{
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
    public function getCustomerConfirmed();

    /**
     * @param int $confirmed
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerConfirmed($confirmed);

    /**
     * @return int
     */
    public function getToken();

    /**
     * @param int $token
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setToken($token);
}