<?php

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class Configurable extends NotificationProductDataResolver implements NotificationProductDataResolverInterface
{
    public function isApplicable($productParentId)
    {
        return !empty($productParentId);
    }

    public function getProductData($subscription)
    {
        $product = $this->getProduct($subscription->getParentProductId(), $subscription->getStoreId());

        return [
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'product_url' => $product->getProductUrl(),
            'product_image_url' => $this->getProductImageUrl($product)
        ];
    }
}
