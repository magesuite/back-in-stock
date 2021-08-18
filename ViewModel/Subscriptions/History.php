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
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \MageSuite\BackInStock\Model\ResourceModel\Product $productResource,
        \Magento\Framework\UrlInterface $url
    ) {
        $this->customerSession = $customerSession;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->notificationProductDataResolver = $notificationProductDataResolver;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->productResource = $productResource;
        $this->url = $url;
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

        $saleableQuantityData = $this->getSalableQuantityDataBySku->execute($sku);

        return $this->isSalableQuantityPositive($saleableQuantityData);
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

    protected function isSalableQuantityPositive($saleableQuantityData)
    {
        foreach ($saleableQuantityData as $saleableQuantityItem) {
            $qty = $saleableQuantityItem['qty'] ?? 0;

            if ($qty > 0) {
                return true;
            }
        }

        return false;
    }
}
