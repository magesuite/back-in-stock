<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class NotificationProductDataResolverPool
{
    public function __construct(
        protected array $productDataResolvers
    ) {
    }

    public function getProductDataResolver(
        int $productParentId
    ): ?\MageSuite\BackInStock\Model\NotificationProductDataResolver\NotificationProductDataResolverInterface {
        foreach ($this->productDataResolvers as $productDataResolver) {
            if (!$productDataResolver->isApplicable($productParentId)) {
                continue;
            }

            return $productDataResolver;
        }

        return null;
    }
}
