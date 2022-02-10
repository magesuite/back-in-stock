<?php

namespace MageSuite\BackInStock\Model\ProductResolver;

class Simple implements ProductResolverInterface
{
    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\Catalog\Model\Product\Type::DEFAULT_TYPE;
    }

    public function canRenderForm($product)
    {
        if (!$product->isSalable()) {
            return true;
        }

        return false;
    }
}
