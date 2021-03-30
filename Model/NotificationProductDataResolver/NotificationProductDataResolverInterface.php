<?php

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

interface NotificationProductDataResolverInterface
{
    /**
     * @param int $productParentId
     * @return bool
     */
    public function isApplicable($productParentId);

    /**
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription
     * @return array
     */
    public function getProductData($subscription);
}
