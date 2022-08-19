<?php

namespace MageSuite\BackInStock\Test\Integration\Controller\Notification;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SubscribeTest extends \Magento\TestFramework\TestCase\AbstractController
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
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $subscriptionRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->subscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItSubscribeCorrectly()
    {
        $email = 'subscribe_test@test.com';

        $product = $this->productRepository->get('simple');
        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $this->assertTrue($this->subscriptionRepository->subscriptionExist($product->getId(), 'customer_email', $email, 1));

        $subscription = $this->getSubscriptionByProductIdAndEmail($product->getId(), $email);
        $this->assertFalse($subscription->isCustomerConfirmed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture admin_store back_in_stock/general/is_confirmation_required 0
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItSubscribeCorrectlyWithoutConfirmation()
    {
        $email = 'subscribe2_test@test.com';

        $product = $this->productRepository->get('simple');
        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $subscription = $this->getSubscriptionByProductIdAndEmail($product->getId(), $email);
        $this->assertTrue($subscription->isCustomerConfirmed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     * @magentoDataFixture loadResetSubscriptions
     * @dataProvider provideResetSubscriptions
     */
    public function testItResetSubscriptionCorrectly(string $email, bool $expectedResult)
    {
        $product = $this->productRepository->get('product_out_of_stock');

        $subscription = $this->subscriptionRepository->get(
            $product->getId(),
            'customer_email',
            $email,
            1
        );

        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $subscription->getProductId(),
            'email' => $subscription->getCustomerEmail()
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $resetedSubscription = $this->subscriptionRepository->getById($subscription->getId());
        $this->assertEquals($expectedResult, $subscription->getToken() !== $resetedSubscription->getToken());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadRemovedSubscriptions
     * @dataProvider provideRemovedSubscriptions
     */
    public function testItCreateNewSubscriptionCorrectlyWhenPreviousIsRemoved(string $email)
    {
        $product = $this->productRepository->get('simple');

        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $newSubscription = $this->getSubscriptionByProductIdAndEmail($product->getId(), $email);
        $this->assertEquals(false, $newSubscription->isRemoved());
    }

    private function getSubscriptionByProductIdAndEmail($productId, $email)
    {
        $storeId = 1;
        return $this->subscriptionRepository->get($productId, 'customer_email', $email, $storeId);
    }

    public static function loadProduct()
    {
        include __DIR__.'/../../../_files/product_out_of_stock.php';
    }

    public static function loadResetSubscriptions()
    {
        include __DIR__.'/../../../_files/reset_subscriptions.php';
    }

    public static function loadRemovedSubscriptions()
    {
        include __DIR__.'/../../../_files/removed_subscriptions.php';
    }

    /**
     * @return array
     */
    public function provideResetSubscriptions(): array
    {
        return [
            ['test+0@test.com', true],
            ['test+1@test.com', false],
            ['test+2@test.com', true],
            ['test+3@test.com', false],
            ['test+4@test.com', true],
            ['test+5@test.com', true],
            ['test+6@test.com', true],
            ['test+7@test.com', false]
        ];
    }

    /**
     * @return array
     */
    public function provideRemovedSubscriptions(): array
    {
        return [
            ['test+0@test.com'],
            ['test+1@test.com'],
            ['test+2@test.com'],
            ['test+3@test.com'],
            ['test+4@test.com'],
            ['test+5@test.com'],
            ['test+6@test.com'],
            ['test+7@test.com']
        ];
    }
}
