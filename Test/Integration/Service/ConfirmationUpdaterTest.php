<?php
namespace MageSuite\BackInStock\Test\Integration\Service;

class ConfirmationUpdaterTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Service\ConfirmationUpdater
     */
    protected $confirmationUpdater;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    protected $backInStockSubscription;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection
     */
    protected $subscriptionCollection;

    public function setUp()
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
     * @magentoDataFixture loadSubscriptions
     */
    public function testItConfirmsSubscriptionCorrectly()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $subscription = $this->subscriptionCollection->addFieldToFilter('product_id', ['eq' => $product->getId()])->getFirstItem();

        $token = $this->backInStockSubscriptionRepository->generateToken($subscription->getCustomerEmail(), (string) $subscription->getCustomerId());

        $this->assertEquals(0, $subscription->getCustomerConfirmed());

        $this->confirmationUpdater->update(['id' => $subscription->getId(), 'token' => $token]);

        $subscription = $this->backInStockSubscriptionRepository->getById($subscription->getId());

        $this->assertEquals(1, $subscription->getCustomerConfirmed());
    }

    public static function loadSubscriptions() {
        include __DIR__.'/../../_files/subscriptions.php';
    }
}