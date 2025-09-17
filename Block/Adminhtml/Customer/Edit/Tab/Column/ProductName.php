<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column;

class ProductName extends \MageSuite\BackInStock\Block\Adminhtml\Customer\Edit\Tab\Column\AbstractColumnRenderer
{
    public function getColumnValue(int $columnId, int $entityId): string
    {
        $backInStock = $this->getBackInStockData($entityId);

        $product = $this->productRepository->getById((int) $backInStock->getProductId());

        return $product->getName();
    }
}
