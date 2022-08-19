<?php

namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column;

abstract class AbstractColumnRenderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected static $backInStockData;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return mixed
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $column = $this->getColumn()->getIndex();

        return $this->getColumnValue($column, $row->getId());
    }

    public function getBackInStockData($entityId)
    {
        if (!isset(self::$backInStockData[$entityId])) {
            self::$backInStockData[$entityId] = $this->backInStockSubscriptionRepository->getById($entityId);
        }

        return self::$backInStockData[$entityId];
    }

    /**
     * @param $columnId
     * @param $entityId
     * @return mixed
     */
    abstract public function getColumnValue($columnId, $entityId);
}
