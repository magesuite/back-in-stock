<?php
namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab;

class Subscriptions extends \Magento\Backend\Block\Widget\Grid\Extended
{

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
    }

    protected function _construct()
    {
        parent::_construct();
        $this->setId('customer_subscriptions_grid');
        $this->setDefaultSort('add_date', 'desc');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no subscriptions for this customer.'));
    }


    protected function _prepareCollection()
    {
        $customerId = $this->_request->getParam('id');
        $collection = $this->subscriptionCollectionFactory->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }


    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'index' => 'product_id',
                'renderer' => \MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column\ProductName::class
            ]
        );

        $this->addColumn('store_id', ['header' => __('Store ID'), 'index' => 'store_id']);

        $this->addColumn('add_date', ['header' => __('Subscription Date'), 'index' => 'add_date', 'type' => 'date']);

        $this->addColumn(
            'send_date',
            ['header' => __('Last Notification Date'), 'index' => 'send_date', 'type' => 'date']
        );

        $this->addColumn('send_count', ['header' => __('Amount of Notifications Sent'), 'index' => 'send_count']);

        return parent::_prepareColumns();
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('backinstock/customer/subscriptions', ['_current' => true]);
    }
}
