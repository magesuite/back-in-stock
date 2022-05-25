<?php

namespace MageSuite\BackInStock\Test\Integration\Service;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class UnsubscriberTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BackInStock\Service\Unsubscriber
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \MageSuite\BackInStock\Service\Unsubscriber
     */
    protected $unsubscriber;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->backInStockSubscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);

        $this->unsubscriber = $this->objectManager->create(\MageSuite\BackInStock\Service\Unsubscriber::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     */
    public function testItMarkAsRemoved()
    {
        $productSku = 'simple';
        $product = $this->productRepository->get($productSku);

        $subscription = $this->backInStockSubscriptionRepository->get($product->getId(), 'customer_email', 'test+0@test.com', 1);
        $this->assertFalse($subscription->isRemoved());

        $this->unsubscriber->execute([$subscription->getId()]);

        $subscription = $this->backInStockSubscriptionRepository->getById($subscription->getId());
        $this->assertTrue($subscription->isRemoved());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoConfigFixture admin_store back_in_stock/general/is_historical_data_kept 0
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions.php
     */
    public function testItRemovedFromDatabase()
    {
        $this->expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $productSku = 'simple';
        $product = $this->productRepository->get($productSku);

        $subscription = $this->backInStockSubscriptionRepository->get($product->getId(), 'customer_email', 'test+0@test.com', 1);
        $this->unsubscriber->execute([$subscription->getId()]);

        $this->backInStockSubscriptionRepository->getById($subscription->getId());
    }
}
