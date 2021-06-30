<?php

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository */
$backInStockSubscriptionRepository = $objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);

/** @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection $backInStockSubscriptionCollection */
$backInStockSubscriptionCollection = $objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);


foreach($backInStockSubscriptionCollection as $subscription)
{
    $subscription->setCustomerConfirmed(true);
    $backInStockSubscriptionRepository->save($subscription);
}
