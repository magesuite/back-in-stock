<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Controller\Notification;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class SubscribeTest extends \Magento\TestFramework\TestCase\AbstractController
{
    protected ?\Magento\TestFramework\ObjectManager $objectManager;
    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository;
    protected ?\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $subscriptionRepository;

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
    public function testItSubscribeCorrectly(): void
    {
        $email = 'subscribe_test@test.com';

        $product = $this->productRepository->get('simple');
        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $this->assertTrue(
            $this->subscriptionRepository->subscriptionExist(
                (int) $product->getId(),
                'customer_email',
                $email,
                1
            )
        );

        $subscription = $this->getSubscriptionByProductIdAndEmail((int)$product->getId(), $email);
        $this->assertFalse($subscription->isCustomerConfirmed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoConfigFixture admin_store back_in_stock/general/is_confirmation_required 0
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItSubscribeCorrectlyWithoutConfirmation(): void
    {
        $email = 'subscribe2_test@test.com';

        $product = $this->productRepository->get('simple');
        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $subscription = $this->getSubscriptionByProductIdAndEmail((int)$product->getId(), $email);
        $this->assertTrue($subscription->isCustomerConfirmed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/product_out_of_stock.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/reset_subscriptions.php
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideResetSubscriptions')]
    public function testItResetSubscriptionCorrectly(string $email, bool $expectedResult): void
    {
        $product = $this->productRepository->get('product_out_of_stock');

        $subscription = $this->subscriptionRepository->get(
            (int) $product->getId(),
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

        $resetedSubscription = $this->subscriptionRepository->getById((int)$subscription->getId());
        $this->assertEquals($expectedResult, $subscription->getToken() !== $resetedSubscription->getToken());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/removed_subscriptions.php
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideRemovedSubscriptions')]
    public function testItCreateNewSubscriptionCorrectlyWhenPreviousIsRemoved(string $email): void
    {
        $product = $this->productRepository->get('simple');

        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => $email
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $newSubscription = $this->getSubscriptionByProductIdAndEmail((int) $product->getId(), $email);
        $this->assertEquals(false, $newSubscription->isRemoved());
    }

    private function getSubscriptionByProductIdAndEmail(int $productId, string $email): \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
    {
        $storeId = 1;

        return $this->subscriptionRepository->get($productId, 'customer_email', $email, $storeId);
    }

    public static function provideResetSubscriptions(): array
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

    public static function provideRemovedSubscriptions(): array
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
