<?php

namespace MageSuite\BackInStock\Model;

class NotificationProductDataResolverPool
{
    protected $productDataResolvers;

    public function __construct(array $productDataResolvers)
    {
        $this->productDataResolvers = $productDataResolvers;
    }

    public function getProductDataResolver($productParentId)
    {
        foreach ($this->productDataResolvers as $productDataResolver) {
            if (!$productDataResolver->isApplicable($productParentId)) {
                continue;
            }

            return $productDataResolver;
        }

        return null;
    }
}
