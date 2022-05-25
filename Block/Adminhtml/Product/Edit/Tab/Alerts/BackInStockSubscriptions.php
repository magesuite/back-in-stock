<?php

namespace MageSuite\BackInStock\Block\Adminhtml\Product\Edit\Tab\Alerts;

class BackInStockSubscriptions extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Module\Manager $moduleManager,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $backendHelper, $data);
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
    }

    /**
     * @return void
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('alertStock');
        $this->setDefaultSort('add_date');
        $this->setDefaultSort('DESC');
        $this->setUseAjax(true);
        $this->setFilterVisibility(false);
        $this->setEmptyText(__('There are no customers for this alert.'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareCollection()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
        if ($store = $this->getRequest()->getParam('store')) {
            $storeId = $this->_storeManager->getStore($store)->getId();
        }
        $collection = $this->subscriptionCollectionFactory->create();
        $collection
            ->addFieldToFilter('product_id', ['eq' => $productId])
            ->addFieldToFilter('is_removed', ['eq' => 0]);

        if ($storeId) {
            $collection
                ->addFieldToFilter('store_id', ['eq' => $storeId]);
        }

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn('customer_id', ['header' => __('Customer ID'), 'index' => 'customer_id']);

        $this->addColumn('customer_email', ['header' => __('Email'), 'index' => 'customer_email']);

        $this->addColumn('notification_channel', ['header' => __('Notification Channel'), 'index' => 'notification_channel']);

        $this->addColumn('add_date', ['header' => __('Subscribe Date'), 'index' => 'add_date', 'type' => 'date']);

        $this->addColumn('send_date', ['header' => __('Last Notified'), 'index' => 'send_date', 'type' => 'date']);

        $this->addColumn('send_count', ['header' => __('Send Count'), 'index' => 'send_count']);

        $this->addColumn('customer_confirmed', ['header' => __('Customer Confirmed'), 'index' => 'customer_confirmed']);

        $this->addColumn('customer_unsubscribed', ['header' => __('Customer Unsubscribed'), 'index' => 'customer_unsubscribed']);

        $this->addColumn('action', [
            'header' => __('Remove'),
            'type' => 'action',
            'getter' => 'getId',
            'actions' => [
                [
                    'caption' => __('Remove'),
                    'url' => [
                        'base' => 'backinstock/product/remove'

                    ],
                    'field' => 'id'
                ]
            ],
            'filter' => false,
            'sortable' => false
        ]);

        return parent::_prepareColumns();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getGridUrl()
    {
        $productId = $this->getRequest()->getParam('id');
        $storeId = $this->getRequest()->getParam('store', 0);
        if ($storeId) {
            $storeId = $this->_storeManager->getStore($storeId)->getId();
        }

        return $this->getUrl('backinstock/product/grid', ['id' => $productId, 'store' => $storeId]);
    }
}
