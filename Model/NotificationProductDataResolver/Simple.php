<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class Simple extends NotificationProductDataResolver implements NotificationProductDataResolverInterface
{
    public function isApplicable(int $productParentId): bool
    {
        return empty($productParentId);
    }

    public function getProductData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): array
    {
        $product = $this->getProduct(
            (int)$subscription->getProductId(),
            (int)$subscription->getStoreId()
        );

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
