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

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->customerSession = $customerSession;
        parent::__construct($context);
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
        $this->productRepository = $productRepository;
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
