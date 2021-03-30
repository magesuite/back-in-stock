<?php
namespace MageSuite\BackInStock\Test\Integration\Controller\Notification;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class UnsubscribeTest extends \Magento\TestFramework\TestCase\AbstractController
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

    /**
     * @var \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    protected $subscription;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->subscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
        $this->subscription = $this->objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItUnsubscribesCorrectly()
    {
        $product = $this->productRepository->get('simple');

        $subscription = $this->subscription;

        $token = $this->subscriptionRepository->generateToken('test@unsubscribe.com', '0');

        $subscription
            ->setCustomerEmail('test@unsubscribe.com')
            ->setCustomerId(0)
            ->setStoreId(1)
            ->setProductId($product->getId())
            ->setToken($token);

        $subscription = $this->subscriptionRepository->save($subscription);

        $this->assertTrue($this->subscriptionRepository->subscriptionExist($product->getId(), 'customer_email', 'test@unsubscribe.com', 1));

        $this->getRequest()->setParams(['id' => $subscription->getId(), 'token' => $token]);

        $this->dispatch('backinstock/notification/unsubscribe');

        $this->assertFalse($this->subscriptionRepository->subscriptionExist($product->getId(), 'customer_email', 'test@unsubscribe.com', 1));
    }
}
