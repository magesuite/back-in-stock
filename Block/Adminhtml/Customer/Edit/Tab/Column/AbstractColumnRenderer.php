<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column;

abstract class AbstractColumnRenderer extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    protected array $backInStockData;

    public function __construct(
        \Magento\Backend\Block\Context $context,
        protected \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * @inheritdoc
     */
    public function render(\Magento\Framework\DataObject $row) //phpcs:ignore
    {
        $column = $this->getColumn()->getIndex();

        return $this->getColumnValue((int) $column, (int) $row->getId());
    }

    public function getBackInStockData(int $entityId): \MageSuite\BackInStock\Model\BackInStockSubscription
    {
        if (!isset($this->backInStockData[$entityId])) {
            $this->backInStockData[$entityId] = $this->backInStockSubscriptionRepository->getById((int) $entityId);
        }

        return $this->backInStockData[$entityId];
    }

    abstract public function getColumnValue(int $columnId, int $entityId): string;
}
