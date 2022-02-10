<?php

namespace MageSuite\BackInStock\Model\ProductResolver;

interface ProductResolverInterface
{
    /**
     * @param string $productTypeId
     * @return bool
     */
    public function isApplicable($productTypeId);

    /**
     * @param $product
     * @return array
     */
    public function canRenderForm($product);
}
