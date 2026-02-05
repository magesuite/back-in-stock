<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Service;

class ConfirmationUpdaterTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\MageSuite\BackInStock\Service\ConfirmationUpdater $confirmationUpdater;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository;
    protected ?\MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription;
    protected ?\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection $subscriptionCollection;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->confirmationUpdater = $this->objectManager->create(\MageSuite\BackInStock\Service\ConfirmationUpdater::class);
        $this->backInStockSubscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
        $this->backInStockSubscription = $this->objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);
        $this->subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     */
    public function testItConfirmsSubscriptionCorrectly(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        $subscription = $this->subscriptionCollection->addFieldToFilter('product_id', ['eq' => $product->getId()])->getFirstItem();

        $this->assertEquals(false, $subscription->isCustomerConfirmed());

        $this->confirmationUpdater->update(['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
        $subscription = $this->backInStockSubscriptionRepository->getById((int)$subscription->getId());

        $this->assertEquals(true, $subscription->isCustomerConfirmed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/expired_subscriptions.php
     */
    public function testItNotConfirmsExpiredSubscription(): void
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');
        $subscription = $this->subscriptionCollection->addFieldToFilter('product_id', ['eq' => $product->getId()])->getFirstItem();

        $this->assertEquals(false, $subscription->isCustomerConfirmed());

        $this->confirmationUpdater->update(['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
        $subscription = $this->backInStockSubscriptionRepository->getById((int)$subscription->getId());

        $this->assertEquals(false, $subscription->isCustomerConfirmed());
    }
}
