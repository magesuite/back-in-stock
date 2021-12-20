<?php
namespace MageSuite\BackInStock\Test\Integration\Model\Queue\Handler;

class AddNotificationToQueueTest extends \PHPUnit\Framework\TestCase
{
    const SOURCE_CODE_DEFAULT = 'default';

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue
     */
    protected $addNotificationToQueue;

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

        $this->addNotificationToQueue = $this->objectManager->create(\MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture loadSubscriptions
     * @magentoDataFixture loadSubscriptionsCustomerConfirmed
     * @magentoDataFixture loadSubscriptionsMarkedRemoved
     */
    public function testItCreateNotificationQueueCorrectly()
    {
        $productSku = 'simple';

        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($productSku);

        $item = [
            $productSku => [
                self::SOURCE_CODE_DEFAULT => [
                    'old_qty' => 0,
                    'new_qty' => 10,
                    'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK
                ]
            ]
        ];

        $this->addNotificationToQueue->execute($item);

        $this->assertEquals(7, $this->notificationCollection->getSize());

        foreach ($this->notificationCollection as $notification) {
            $this->assertEquals('automatic_notification', $notification->getNotificationType());
        }
    }

    public static function loadSubscriptions()
    {
        include __DIR__.'/../../../../_files/subscriptions.php';
    }

    public static function loadSubscriptionsCustomerConfirmed()
    {
        include __DIR__.'/../../../../_files/subscriptions_confirmed_customer.php';
    }

    public static function loadSubscriptionsMarkedRemoved()
    {
        include __DIR__.'/../../../../_files/subscriptions_marked_removed.php';
    }
}
