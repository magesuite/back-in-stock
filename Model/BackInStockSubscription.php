<?php

namespace MageSuite\BackInStock\Model;

class BackInStockSubscription extends \Magento\Framework\Model\AbstractModel implements \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
{
    public const SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS = 24;

    /**
     * @var \MageSuite\BackInStock\Helper\Subscription
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->subscriptionHelper = $subscriptionHelper;
    }

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
     * @return string
     */
    public function getSendNotificationStatus()
    {
        return $this->getData('send_notification_status');
    }

    /**
     * @param string $sendNotificationStatus
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function setSendNotificationStatus($sendNotificationStatus)
    {
        $this->setData('send_notification_status', $sendNotificationStatus);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isCustomerConfirmed(): bool
    {
        return $this->getData('customer_confirmed');
    }

    /**
     * @inheritDoc
     */
    public function setCustomerConfirmed(bool $confirmed)
    {
        $this->setData('customer_confirmed', $confirmed);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isCustomerUnsubscribed(): bool
    {
        return $this->getData('customer_unsubscribed');
    }

    /**
     * @inheritDoc
     */
    public function setCustomerUnsubscribed(bool $unsubscribed)
    {
        $this->setData('customer_unsubscribed', $unsubscribed);

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

    /**
     * @inheritDoc
     */
    public function isRemoved(): bool
    {
        return $this->getData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::IS_REMOVED);
    }

    /**
     * @inheritDoc
     */
    public function setIsRemoved(bool $isRemoved)
    {
        return $this->setData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::IS_REMOVED, $isRemoved);
    }

    public function isConfirmationDeadlinePassed(): bool
    {
        return $this->subscriptionHelper->isConfirmationDeadlinePassed($this->getAddDate());
    }
}
