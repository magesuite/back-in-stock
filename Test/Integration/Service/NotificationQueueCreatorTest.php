<?php
namespace MageSuite\BackInStock\Test\Integration\Service;

class NotificationQueueCreatorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueCreator
     */
    protected $notificationQueueCreator;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;


    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification\Collection
     */
    protected $notificationCollection;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->notificationQueueCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueCreator::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     */
    public function testItCreateNotificationQueueCorrectly()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection;

        $this->assertEquals(10, $notificationCollection->getSize());

        foreach ($notificationCollection as $notification){
            $this->assertEquals('automatic_notification', $notification->getNotificationType());
            $this->assertEquals('test message', $notification->getMessage());
        }
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadRemovedSubscriptions
     */
    public function testItDoesNotAddRemovedSubscriptionToNotificationQueue()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        $this->notificationQueueCreator->addNotificationsToQueue($product->getId(), 1, \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION, 'test message');

        $notificationCollection = $this->notificationCollection;

        $this->assertEquals(0, $notificationCollection->getSize());
    }

    public static function loadSubscriptions()
    {
        include __DIR__.'/../../_files/subscriptions.php';
    }

    public static function loadSubscriptionsCustomerConfirmed()
    {
        include __DIR__.'/../../_files/subscriptions_confirmed_customer.php';
    }

    public static function loadRemovedSubscriptions()
    {
        include __DIR__.'/../../_files/removed_subscriptions.php';
    }
}
