<?php

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository */
$backInStockSubscriptionRepository = $objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get('simple');

$removedSubscriptions = [1, 3, 8];

foreach ($removedSubscriptions as $i) {
    /** @var  \MageSuite\BackInStock\Model\BackInStockSubscription $subscription */
    $subscription = $backInStockSubscriptionRepository->get($product->getId(), 'customer_email', 'test+'. $i .'@test.com', 1);
    $subscription->setIsRemoved(true);
    $backInStockSubscriptionRepository->save($subscription);
}
