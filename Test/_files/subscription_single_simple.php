<?php

\Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize();

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);


/** @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository */
$backInStockSubscriptionRepository = $objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);

/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get('simple');

$token = $backInStockSubscriptionRepository->generateToken('testsubscription@test.com', '0');
/** @var \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription */
$backInStockSubscription = $objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);

$backInStockSubscription
    ->setProductId($product->getId())
    ->setStoreId(1)
    ->setCustomerId(0)
    ->setCustomerEmail('testsubscription@test.com')
    ->setToken($token);

$backInStockSubscriptionRepository->save($backInStockSubscription);
