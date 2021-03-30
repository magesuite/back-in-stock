<?php
namespace MageSuite\BackInStock\Model;

class BackInStockSubscription extends \Magento\Framework\Model\AbstractModel implements \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
{
    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription::class);
    }

    public function setId($id)
    {
        $this->setData('id', $id);

        return $this;
    }

    public function getId()
    {
        return $this->getData('id');
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getData('customer_id');
    }

    /**
     * @param int $customerId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer_id', $customerId);

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerEmail()
    {
        return $this->getData('customer_email');
    }

    /**
     * @param int $customerEmail
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->setData('customer_email', $customerEmail);

        return $this;
    }

    /**
     * @return string
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @param string $productId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);

        return $this;
    }

    /**
     * @return string
     */
    public function getParentProductId()
    {
        return $this->getData('parent_product_id');
    }

    /**
     * @param string $parentProductId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setParentProductId($parentProductId)
    {
        $this->setData('parent_product_id', $parentProductId);

        return $this;
    }

    /**
     * @return string
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @param string $storeId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);

        return $this;
    }

    /**
     * @return string
     */
    public function getAddDate()
    {
        return $this->getData('add_date');
    }

    /**
     * @param string $addDate
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setAddDate($addDate)
    {
        $this->setData('add_date', $addDate);

        return $this;
    }

    /**
     * @return int
     */
    public function getSendDate()
    {
        return $this->getData('send_date');
    }

    /**
     * @param int $sendDate
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendDate($sendDate)
    {
        $this->setData('send_date', $sendDate);

        return $this;
    }

    /**
     * @return int
     */
    public function getSendCount()
    {
        return $this->getData('send_count');
    }

    /**
     * @param int $sendCount
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendCount($sendCount)
    {
        $this->setData('send_count', $sendCount);

        return $this;
    }

    /**
     * @return int
     */
    public function getWasNotificationSent()
    {
        return $this->getData('was_notification_sent');
    }

    /**
     * @param int $wasNotificationSent
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setWasNotificationSent($wasNotificationSent)
    {
        $this->setData('was_notification_sent', $wasNotificationSent);

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerConfirmed()
    {
        return $this->getData('customer_confirmed');
    }

    /**
     * @param int $confirmed
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setCustomerConfirmed($confirmed)
    {
        $this->setData('customer_confirmed', $confirmed);

        return $this;
    }

    /**
     * @return int
     */
    public function getToken()
    {
        return $this->getData('token');
    }

    /**
     * @param int $token
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setToken($token)
    {
        $this->setData('token', $token);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getNotificationChannel()
    {
        return $this->getData('notification_channel');
    }

    /**
     * @inheritDoc
     */
    public function setNotificationChannel($channel)
    {
        return $this->setData('notification_channel', $channel);
    }
}
