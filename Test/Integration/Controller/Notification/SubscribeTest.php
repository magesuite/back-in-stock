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
        $product = $this->productRepository->get('simple');
        $this->getRequest()->setParams([
            'notification_channel' => 'email',
            'product' => $product->getId(),
            'email' => 'subscribe_test@test.com'
        ]);

        $this->dispatch('backinstock/notification/subscribe');

        $this->assertTrue($this->subscriptionRepository->subscriptionExist($product->getId(), 'customer_email', 'subscribe_test@test.com', 1));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture loadProduct
     * @magentoDataFixture loadSubscriptions
     * @dataProvider provideSubscriptions
     */
    public function testItResetSubscriptionCorrectly(string $email, bool $expectedResult)
    {
        $product = $this->productRepository->get('product_out_of_stock');

        $subscription =  $this->subscriptionRepository->get(
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

    public static function loadProduct()
    {
        include __DIR__.'/../../../_files/product_out_of_stock.php';
    }

    public static function loadSubscriptions()
    {
        include __DIR__.'/../../../_files/reset_subscriptions.php';
    }

    /**
     * @return array
     */
    public function provideSubscriptions(): array
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
}
