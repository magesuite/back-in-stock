<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class BackInStockSubscription extends \Magento\Framework\Model\AbstractModel implements \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
{
    public const SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS = 24;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        ?\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        ?\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

    }

    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription::class);
    }

    public function getId()
    {
        return $this->_getData('id');
    }

    public function setId($id)
    {
        $this->setData('id', $id);

        return $this;
    }

    public function getCustomerId()
    {
        return $this->_getData('customer_id');
    }

    public function setCustomerId($customerId)
    {
        return $this->setData('customer_id', $customerId);
    }

    public function getCustomerEmail()
    {
        return $this->_getData('customer_email');
    }

    public function setCustomerEmail($customerEmail)
    {
        return $this->setData('customer_email', $customerEmail);
    }

    public function getProductId()
    {
        return $this->_getData('product_id');
    }

    public function setProductId($productId)
    {
        return $this->setData('product_id', $productId);
    }

    public function getParentProductId()
    {
        return $this->_getData('parent_product_id');
    }

    public function setParentProductId($parentProductId)
    {
        return $this->setData('parent_product_id', $parentProductId);
    }

    public function getStoreId()
    {
        return $this->_getData('store_id');
    }

    public function setStoreId($storeId)
    {
        return $this->setData('store_id', $storeId);
    }

    public function getAddDate()
    {
        return $this->_getData('add_date');
    }

    public function setAddDate($addDate)
    {
        return $this->setData('add_date', $addDate);
    }

    public function getSendDate()
    {
        return $this->_getData('send_date');
    }

    public function setSendDate($sendDate)
    {
        return $this->setData('send_date', $sendDate);
    }

    public function getSendCount()
    {
        return $this->_getData('send_count');
    }

    public function setSendCount($sendCount)
    {
        return $this->setData('send_count', $sendCount);
    }

    public function getSendNotificationStatus()
    {
        return $this->_getData('send_notification_status');
    }

    public function setSendNotificationStatus($sendNotificationStatus)
    {
        return $this->setData('send_notification_status', $sendNotificationStatus);
    }

    public function isCustomerConfirmed(): bool
    {
        return (bool)$this->_getData('customer_confirmed');
    }

    public function setCustomerConfirmed(bool $confirmed)
    {
        return $this->setData('customer_confirmed', $confirmed);
    }

    public function isCustomerUnsubscribed(): bool
    {
        return (bool)$this->_getData('customer_unsubscribed');
    }

    public function setCustomerUnsubscribed(bool $unsubscribed)
    {
        return $this->setData('customer_unsubscribed', $unsubscribed);
    }

    public function getToken()
    {
        return $this->_getData('token');
    }

    public function setToken($token)
    {
        return $this->setData('token', $token);
    }

    public function getNotificationChannel()
    {
        return $this->_getData('notification_channel');
    }

    public function setNotificationChannel($channel)
    {
        return $this->setData('notification_channel', $channel);
    }

    public function isRemoved(): bool
    {
        return (bool)$this->getData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::IS_REMOVED);
    }

    public function setIsRemoved(bool $isRemoved)
    {
        return $this->setData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::IS_REMOVED, $isRemoved);
    }

    public function isConfirmationDeadlinePassed(): bool
    {
        return $this->subscriptionHelper->isConfirmationDeadlinePassed($this->getAddDate());
    }

    public function getSendCountDaily(): int
    {
        return (int)$this->_getData(self::SEND_COUNT_DAILY);
    }

    public function setSendCountDaily(int $sendCountDaily): \MageSuite\BackInStock\Model\BackInStockSubscription
    {
        return $this->setData(self::SEND_COUNT_DAILY, $sendCountDaily);
    }

    public function getProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        if ($this->hasData('product')) {
            return $this->getData('product');
        }

        try {
            $product = $this->productRepository->getById($this->getProductId(), false, (int)$this->getStoreId());
            $this->setProduct($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return null;
        }

        return $this->getData('product');
    }

    public function getParentProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        if ($this->hasData('parent_product') || !$this->getParentProductId()) {
            return $this->getData('parent_product');
        }

        try {
            $product = $this->productRepository->getById($this->getParentProductId(), false, (int)$this->getStoreId());
            $this->setParentProduct($product);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) {
            return null;
        }

        return $this->getData('parent_product');
    }
}
