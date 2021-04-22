<?php
namespace MageSuite\BackInStock\Block\Subscriptions;

class History extends \Magento\Framework\View\Element\Template
{
    protected $subscriptions;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;
    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface
     */
    protected $notificationProductDataResolver;

    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->productRepository = $productRepository;
        $this->notificationProductDataResolver = $notificationProductDataResolver;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
    }


    public function getNotifications()
    {
        if (!($customerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->subscriptions) {
            $this->subscriptions = $this->subscriptionCollectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_confirmed', ['eq' => 1])
                ->addFieldToFilter('customer_id', ['eq' => $customerId])
                ->setOrder(
                    'add_date',
                    'desc'
                );
        }
        return $this->subscriptions;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getNotifications()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'backinstock.history.pager'
            )->setCollection(
                $this->getNotifications()
            );
            $this->setChild('pager', $pager);
            $this->getNotifications()->load();
        }
        return $this;
    }

    public function getProduct($id)
    {
        return $this->productRepository->getById($id);
    }

    public function isProductSaleable($product)
    {
        $isSaleable = $product->getIsSalable();

        if (!$isSaleable) {
            return $isSaleable;
        }

        $saleableQuantityData = $this->getSalableQuantityDataBySku->execute($product->getSku());
        $qty = $saleableQuantityData[0]['qty'] ?? null;

        if ($qty === null) {
            return $isSaleable;
        }

        return $qty > 0;
    }

    public function getProductUrl($product, $notification)
    {
        if (!$notification->getParentProductId()) {
            return $product->getProductUrl();
        }

        $parentProductData = $this->notificationProductDataResolver->getProductData($notification);

        if (empty($parentProductData)) {
            return null;
        }

        return $parentProductData->getProductUrl();
    }

    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getUnsubscribeUrl($notification)
    {
        return $this->getUrl('backinstock/notification/unsubscribe', ['id' => $notification->getId(), 'token' => $notification->getToken()]);
    }

    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }
}
