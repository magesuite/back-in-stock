<?php

namespace MageSuite\BackInStock\ViewModel\Subscriptions;

class History implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        protected \Magento\Customer\Model\Session $customerSession,
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        protected \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        protected \MageSuite\BackInStock\Model\ResourceModel\Product $productResource,
        protected \Magento\Framework\UrlInterface $url,
        protected \Magento\Store\Model\StoreManager $storeManager,
        protected \Magento\InventorySales\Model\StockResolver $stockResolver,
        protected \Magento\InventorySales\Model\GetProductSalableQty $getProductSalableQty,
        protected \Psr\Log\LoggerInterface $logger
    ) {
    }

    public function getSubscriptions()
    {
        $customerId = $this->customerSession->getCustomerId();

        if (empty($customerId)) {
            return [];
        }

        return $this->subscriptionCollectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_confirmed', ['eq' => 1])
            ->addFieldToFilter('customer_unsubscribed', ['eq' => 0])
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('is_removed', ['eq' => 0])
            ->setOrder('add_date', 'desc');
    }

    public function getProductData(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): \Magento\Framework\DataObject
    {
        return $this->notificationProductDataResolver->getProductData($subscription);
    }

    public function isProductSaleable(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): bool
    {
        $sku = $this->productResource->getSkuByProductId($subscription->getProductId());

        if (!$sku) {
            return false;
        }

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stockId = $this->stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

        try {
            $qty = $this->getProductSalableQty->execute($sku, $stockId);
            return $qty > 0;
        } catch (\Exception $e) {
            $this->logger->critical(
                __('Error checking product salable status for SKU %1: %2', $sku, $e->getMessage())
            );
            return false;
        }
    }

    public function getUnsubscribeUrl(\MageSuite\BackInStock\Model\BackInStockSubscription $notification): string
    {
        return $this->url->getUrl(
            'backinstock/notification/unsubscribe',
            [
                'id' => $notification->getId(),
                'token' => $notification->getToken()
            ]
        );
    }
}
