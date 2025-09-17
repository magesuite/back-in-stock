<?php

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

interface NotificationProductDataResolverInterface
{
    /**
     * @param int $productParentId
     * @return bool
     */
    public function isApplicable(int $productParentId): bool;

    /**
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription
     * @return array
     */
    public function getProductData(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): array;
}
