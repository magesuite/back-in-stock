<?php

namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column;

class ProductName extends \MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column\AbstractColumnRenderer
{
    /**
     * @param $columnId
     * @param $entityId
     * @return mixed
     */
    public function getColumnValue($columnId, $entityId)
    {
        $backInStock = $this->getBackInStockData($entityId);

        $product = $this->productRepository->getById($backInStock->getProductId());

        return $product->getName();
    }
}