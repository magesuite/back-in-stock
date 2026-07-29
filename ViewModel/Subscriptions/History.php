<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\ViewModel\Subscriptions;

class History implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        protected \Magento\Customer\Model\Session $customerSession,
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        protected \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        protected \Magento\Framework\UrlInterface $url,
        protected \Psr\Log\LoggerInterface $logger
    ) {}

    public function getSubscriptions(): \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection
    {
        $customerId = (int)$this->customerSession->getCustomerId();

        return $this->subscriptionCollectionFactory
            ->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_confirmed', ['eq' => 1])
            ->addFieldToFilter('customer_unsubscribed', ['eq' => 0])
            ->addFieldToFilter('customer_id', ['eq' => $customerId])
            ->addFieldToFilter('is_removed', ['eq' => 0])
            ->setOrder('add_date', 'desc')
            ->addProductsToCollection();
    }

    public function getProductData(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): \Magento\Framework\DataObject
    {
        return $this->notificationProductDataResolver->getProductData($subscription);
    }

    public function isProductSaleable(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): bool
    {
        $product = $subscription->getProduct();

        if (!$product) {
            return false;
        }

        return $product->isSaleable();
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
