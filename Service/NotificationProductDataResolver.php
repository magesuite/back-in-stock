<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service;

class NotificationProductDataResolver implements \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\NotificationProductDataResolverPool $notificationProductDataResolverPool
    ) {
    }

    public function getProductData(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription
    ): \Magento\Framework\DataObject {
        $result = new \Magento\Framework\DataObject();
        $notificationProductDataResolver = $this->notificationProductDataResolverPool->getProductDataResolver(
            (int) $subscription->getParentProductId()
        );

        if (!$notificationProductDataResolver) {
            return $result;
        }

        $productData = $notificationProductDataResolver->getProductData($subscription);
        $result->setData($productData);

        return $result;
    }
}
