<?php

namespace MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Grid;

class Collection extends \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection implements \Magento\Framework\Api\Search\SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param mixed|null $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed $eventObject
     * @param mixed $resourceModel
     * @param string $model
     * @param null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = \Magento\Framework\View\Element\UiComponent\DataProvider\Document::class,
        $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * {@inheritdoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritdoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    protected function joinStatusColumn()
    {
        $columnExpr = new \Zend_Db_Expr('IF(send_notification_status != "", send_notification_status, "Not send")');

        $statusTable = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), ['id','status' => $columnExpr]);

        $this->getSelect()
            ->joinLeft(
                ['st' => $statusTable],
                'main_table.id = st.id',
                ['status' => 'st.status']
            );
    }

    protected function joinProductNameColumn()
    {
        $attributeIdSelect = $this->getConnection()
            ->select()
            ->from(['ea' => $this->getConnection()->getTableName('eav_attribute')], ['ea.attribute_id'])
            ->joinLeft(['eet' => $this->getConnection()->getTableName('eav_entity_type')], 'ea.entity_type_id = eet.entity_type_id')
            ->where('ea.attribute_code = ?', \Magento\Catalog\Api\Data\ProductInterface::NAME)
            ->where('eet.entity_type_code = ?', \Magento\Catalog\Model\Product::ENTITY);

        $attributeId = $this->getConnection()->fetchOne($attributeIdSelect);

        $this->getSelect()
            ->joinLeft(
                ['cpe' => $this->getTable('catalog_product_entity_varchar')],
                'main_table.product_id = cpe.entity_id',
                ['product_name' => 'cpe.value']
            )->where('cpe.attribute_id = ? and cpe.store_id = 0', $attributeId);
    }

    protected function joinConfirmationStatusColumn()
    {
        $columnExpr = sprintf(
            "(CASE
                WHEN customer_confirmed = '1' AND customer_unsubscribed = '0' THEN '%s'
                WHEN customer_unsubscribed = '1' THEN '%s'
                WHEN customer_confirmed = '0' AND customer_unsubscribed = '0' AND DATE_ADD(add_date, INTERVAL %s HOUR) < NOW() THEN '%s'
                ELSE '%s'
            END)",
            \MageSuite\BackInStock\Ui\Component\Listing\Column\ConfirmationStatus::STATUS_CONFIRMED,
            \MageSuite\BackInStock\Ui\Component\Listing\Column\ConfirmationStatus::STATUS_UNSUBSCRIBED,
            \MageSuite\BackInStock\Model\BackInStockSubscription::SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS,
            \MageSuite\BackInStock\Ui\Component\Listing\Column\ConfirmationStatus::STATUS_REJECTED,
            \MageSuite\BackInStock\Ui\Component\Listing\Column\ConfirmationStatus::STATUS_PENDING
        );

        $confirnationStatusTable = $this->getConnection()
            ->select()
            ->from($this->getMainTable(), ['id','confirmation_status' =>  new \Zend_Db_Expr($columnExpr)]);

        $this->getSelect()
            ->joinLeft(
                ['cst' => $confirnationStatusTable],
                'main_table.id = cst.id',
                ['confirmation_status' => 'cst.confirmation_status']
            );
    }

    protected function _renderFiltersBefore()
    {
        $this->getSelect()->where('main_table.is_removed = 0');
        $this->joinStatusColumn();
        $this->joinProductNameColumn();
        $this->joinConfirmationStatusColumn();

        parent::_renderFiltersBefore();
    }
}
