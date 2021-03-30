<?php

namespace MageSuite\BackInStock\Api;

interface NotificationProductDataResolverInterface
{
    /**
     * @var \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription
     * @return \Magento\Framework\DataObject
     */
    public function getProductData($subscription);
}
