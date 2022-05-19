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

    protected $subscriptionCollection;

    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->subscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
        $this->subscription = $this->objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);
        $this->subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
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

        $this->assertEquals(true, $this->subscriptionRepository->getById($subscription->getId())->isCustomerUnsubscribed());
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItNotUnsubscribesRemovedSubscription()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $token = $this->subscriptionRepository->generateToken('test+u@confirm.com', '0');

        $subscription = $this->subscription;

        $subscription
            ->setCustomerEmail('test+u@confirm.com')
            ->setCustomerId(0)
            ->setStoreId(1)
            ->setProductId($product->getId())
            ->setCustomerUnsubscribed(false)
            ->setIsRemoved(true)
            ->setToken($token);

        $subscription = $this->subscriptionRepository->save($subscription);

        $this->assertEquals(false, $subscription->isCustomerUnsubscribed());
        $this->assertFalse($this->subscriptionRepository->subscriptionExist($product->getId(), 'customer_email', 'test+u@confirm.com', 1));

        $this->getRequest()->setParams(['id' => $subscription->getId(), 'token' => $subscription->getToken()]);

        $this->dispatch('backinstock/notification/unsubscribe');

        $this->assertEquals(false, $this->subscriptionRepository->getById($subscription->getId())->isCustomerUnsubscribed());
    }

    public static function loadExpiredSubscriptions()
    {
        include __DIR__.'/../../../_files/expired_subscriptions.php';
    }
}
