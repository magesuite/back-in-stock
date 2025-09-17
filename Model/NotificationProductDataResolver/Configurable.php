<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class Configurable extends NotificationProductDataResolver implements NotificationProductDataResolverInterface
{
    public function isApplicable(int $productParentId): bool
    {
        return !empty($productParentId);
    }

    public function getProductData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): array
    {
        $product = $this->getProduct(
            (int)$subscription->getParentProductId(),
            (int)$subscription->getStoreId()
        );

        return [
            'name' => $product->getName(),
            'sku' => $product->getSku(),
            'product_url' => $product->getProductUrl(),
            'product_image_url' => $this->getProductImageUrl($product)
        ];
    }
}
