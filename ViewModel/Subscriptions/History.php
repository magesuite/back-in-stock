<?php
namespace MageSuite\BackInStock\ViewModel\Subscriptions;

class History implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    /**
     * @var \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface
     */
    protected $notificationProductDataResolver;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    /**
     * @var \Magento\InventorySales\Model\StockResolver
     */
    protected $stockResolver;

    /**
     * @var \Magento\InventorySales\Model\GetProductSalableQty
     */
    protected $getProductSalableQty;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        \MageSuite\BackInStock\Model\ResourceModel\Product $productResource,
        \Magento\Framework\UrlInterface $url,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\InventorySales\Model\StockResolver $stockResolver,
        \Magento\InventorySales\Model\GetProductSalableQty $getProductSalableQty
    ) {
        $this->customerSession = $customerSession;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->notificationProductDataResolver = $notificationProductDataResolver;
        $this->productResource = $productResource;
        $this->url = $url;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->getProductSalableQty = $getProductSalableQty;
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

    public function getProductData($subscription)
    {
        return $this->notificationProductDataResolver->getProductData($subscription);
    }

    public function isProductSaleable($subscription)
    {
        $sku = $this->productResource->getSkuByProductId($subscription->getProductId());

        if (!$sku) {
            return false;
        }

        $websiteCode = $this->storeManager->getWebsite()->getCode();
        $stockId = $this->stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();

        $qty = $this->getProductSalableQty->execute($sku, $stockId);
        return $qty > 0;
    }

    public function getUnsubscribeUrl($notification)
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
