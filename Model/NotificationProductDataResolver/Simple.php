<?php

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class Simple extends NotificationProductDataResolver implements NotificationProductDataResolverInterface
{
    public function isApplicable($productParentId)
    {
        return empty($productParentId);
    }

    public function getProductData($subscription)
    {
        $product = $this->getProduct($subscription->getProductId(), $subscription->getStoreId());

        if (empty($product)) {
            return [];
        }

        return [
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'product_url' => $product->getProductUrl(),
            'product_image_url' => $this->getProductImageUrl($product)
        ];
    }
}
