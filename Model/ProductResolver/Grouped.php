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
        $allAssignedProductIdsGroupedByType = $product->getTypeInstance()->getChildrenIds($product->getId());
        $allAssignedProductIds = $allAssignedProductIdsGroupedByType[\Magento\GroupedProduct\Model\ResourceModel\Product\Link::LINK_TYPE_GROUPED] ?? [];

        $salableAssignedProductCount = 0;

        foreach ($product->getTypeInstance()->getAssociatedProducts($product) as $associatedProduct) {
            if (!$associatedProduct->isSalable()) {
                continue;
            }

            $salableAssignedProductCount++;
        }

        if ($salableAssignedProductCount < count($allAssignedProductIds)) {
            return true;
        }

        return false;
    }
}
