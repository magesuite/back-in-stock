<?php

namespace MageSuite\BackInStock\Model\ProductResolver;

class Grouped implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE;
    }

    public function canRenderForm($product)
    {
        $allAssignedProductIds = $product->getTypeInstance()->getChildrenIds($product->getId());
        $salableAssignedProductIds = $product->getTypeInstance()->getAssociatedProductIds($product);

        if (count($salableAssignedProductIds) < count($allAssignedProductIds)) {
            return true;
        }

        return false;
    }
}
