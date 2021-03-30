<?php

namespace MageSuite\BackInStock\Service;

class NotificationProductDataResolver implements \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface
{
    /**
     * @var \MageSuite\BackInStock\Model\NotificationProductDataResolverPool
     */
    protected $notificationProductDataResolverPool;

    public function __construct(\MageSuite\BackInStock\Model\NotificationProductDataResolverPool $notificationProductDataResolverPool)
    {
        $this->notificationProductDataResolverPool = $notificationProductDataResolverPool;
    }

    public function getProductData($subscription)
    {
        $result = new \Magento\Framework\DataObject();
        $notificationProductDataResolver = $this->notificationProductDataResolverPool->getProductDataResolver($subscription->getParentProductId());

        if (!$notificationProductDataResolver) {
            return $result;
        }

        $productData = $notificationProductDataResolver->getProductData($subscription);
        $result->setData($productData);

        return $result;
    }
}
