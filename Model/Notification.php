<?php
namespace MageSuite\BackInStock\Model;

class Notification extends \Magento\Framework\Model\AbstractModel implements \MageSuite\BackInStock\Api\Data\NotificationInterface
{
    protected function _construct()
    {
        $this->_init('MageSuite\BackInStock\Model\ResourceModel\Notification');
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

    public function setSubscriptionId($id)
    {
        $this->setData('subscription_id', $id);

        return $this;
    }

    public function getSubscriptionId()
    {
        return $this->getData('subscription_id');
    }

    /**
     * @return string
     */
    public function getCustomerEmail()
    {
        return $this->getData('customer_email');
    }

    /**
     * @param int $customerEmail
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setCustomerEmail($customerEmail)
    {
        $this->setData('customer_email', $customerEmail);

        return $this;
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
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setCustomerId($customerId)
    {
        $this->setData('customer_id', $customerId);

        return $this;
    }

    /**
     * @return int
     */
    public function getStoreId()
    {
        return $this->getData('store_id');
    }

    /**
     * @param int $storeId
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setStoreId($storeId)
    {
        $this->setData('store_id', $storeId);

        return $this;
    }

    /**
     * @return int
     */
    public function getProductId()
    {
        return $this->getData('product_id');
    }

    /**
     * @param int $productId
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setProductId($productId)
    {
        $this->setData('product_id', $productId);

        return $this;
    }

    /**
     * @return string
     */
    public function getNotificationType()
    {
        return $this->getData('notification_type');
    }

    /**
     * @param string $notificationType
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setNotificationType($notificationType)
    {
        $this->setData('notification_type', $notificationType);

        return $this;
    }

    /**
     * @return int
     */
    public function getMessage()
    {
        return $this->getData('message');
    }

    /**
     * @param int $message
     * @return \MageSuite\BackInStock\Model\Notification
     */
    public function setMessage($message)
    {
        $this->setData('message', $message);

        return $this;
    }
}