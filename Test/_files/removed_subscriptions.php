<?php

/** @var \Magento\TestFramework\ObjectManager $objectManager */
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

/** @var \Magento\Catalog\Api\ProductRepositoryInterface $productRepository */
$productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

/** @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository */
$backInStockSubscriptionRepository = $objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);

/** @var \Magento\Catalog\Model\Product $product */
$product = $productRepository->get('simple');

$currentDateTime = new \DateTime();
$subscriptionAddDate =  $currentDateTime->sub(
    new \DateInterval('PT6H')
);
$subscriptionAddDateFormatted = $subscriptionAddDate->format('Y-m-d H:i:s');
$subscriptionsData = [
    ['confirmed' => true, 'unsubscribed' => true, 'removed' => true, 'add_date' => '2020-07-31 09:15:15'],
    ['confirmed' => true, 'unsubscribed' => false, 'removed' => true, 'add_date' => '2020-07-31 09:15:15'],
    ['confirmed' => true, 'unsubscribed' => true, 'removed' => true, 'add_date' => $subscriptionAddDateFormatted],
    ['confirmed' => true, 'unsubscribed' => false, 'removed' => true, 'add_date' => $subscriptionAddDateFormatted],
    ['confirmed' => false, 'unsubscribed' => true, 'removed' => true, 'add_date' => '2020-07-31 09:15:15'],
    ['confirmed' => false, 'unsubscribed' => true, 'removed' => true, 'add_date' => $subscriptionAddDateFormatted],
    ['confirmed' => false, 'unsubscribed' => false, 'removed' => true, 'add_date' => '2020-07-31 09:15:15'],
    ['confirmed' => false, 'unsubscribed' => false, 'removed' => true,'add_date' => $subscriptionAddDateFormatted]

];

for ($i = 0; $i < 8; $i++) {

    $email = 'test+'. $i .'@test.com';
    $token = $backInStockSubscriptionRepository->generateToken($email, '0');

    /** @var \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription */
    $backInStockSubscription = $objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);

    $backInStockSubscription
        ->setProductId($product->getId())
        ->setStoreId(1)
        ->setCustomerId(0)
        ->setCustomerEmail($email)
        ->setCustomerConfirmed($subscriptionsData[$i]['confirmed'])
        ->setCustomerUnsubscribed($subscriptionsData[$i]['unsubscribed'])
        ->setIsRemoved($subscriptionsData[$i]['removed'])
        ->setAddDate($subscriptionsData[$i]['add_date'])
        ->setToken($token);

    $backInStockSubscriptionRepository->save($backInStockSubscription);
}
