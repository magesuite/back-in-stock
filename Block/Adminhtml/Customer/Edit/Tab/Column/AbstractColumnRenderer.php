<?php
namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column;

abstract class AbstractColumnRenderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    static $backInStockData;
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
    )
    {
        parent::__construct($context, $data);
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * @param \Magento\Framework\DataObject $row
     * @return mixed|string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $column = $this->getColumn()->getIndex();

        $value = $this->getColumnValue($column, $row->getId());

        return $value;
    }


    public function getBackInStockData($entityId)
    {
        if(!isset(self::$backInStockData[$entityId])) {
            self::$backInStockData[$entityId] = $this->backInStockSubscriptionRepository->getById($entityId);
        }

        return self::$backInStockData[$entityId];
    }

    /**
     * @param $columnId
     * @param $entityId
     * @return mixed|string
     */
    abstract public function getColumnValue($columnId, $entityId);
}