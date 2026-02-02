<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Model\Queue\Handler;

class AddNotificationToQueueDuplicatePreventionTest extends \PHPUnit\Framework\TestCase
{
    protected const SOURCE_CODE_DEFAULT = 'default';
    protected const STOCK_DEFAULT_ID = 1;

    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;

    protected ?\Magento\Catalog\Api\ProductRepositoryInterface $productRepository = null;

    protected ?\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection $notificationCollection = null;

    protected ?\MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue $addNotificationToQueue = null;

    protected ?\MageSuite\BackInStock\Model\AreProductsSalable $areProductsSalable = null;

    protected ?\MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses $getSalableStatusesStub = null;

    protected ?\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository = null;

    protected ?\MageSuite\BackInStock\Service\NotificationQueueSender $notificationQueueSender = null;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);
        $this->backInStockSubscriptionRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface::class);
        $this->notificationQueueSender = $this->objectManager->create(\MageSuite\BackInStock\Service\NotificationQueueSender::class);

        $this->getSalableStatusesStub = $this->getMockBuilder(\MageSuite\BackInStock\Model\SourceItem\GetSalableStatuses::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->areProductsSalable = $this->objectManager->create(
            \MageSuite\BackInStock\Model\AreProductsSalable::class,
            ['getSalableStatuses' => $this->getSalableStatusesStub]
        );

        $this->addNotificationToQueue = $this->objectManager->create(
            \MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue::class,
            ['areProductsSalable' => $this->areProductsSalable]
        );
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItPreventsDuplicateNotificationsInQueue()
    {
        $productSku = 'simple';
        $item = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 0,
                        'new_qty' => 10,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => false
                ]
            ]
        ];

        $this->assertEquals(0, $this->notificationCollection->getSize());

        $statuses = [
            $productSku => [
                self::STOCK_DEFAULT_ID => true
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        // First call - should add notification
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'First notification should be added to queue');

        // Second call with same data - should NOT add duplicate
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'Duplicate notification should not be added to queue');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItPreventsNotificationsForAlreadySentSubscriptions()
    {
        $productSku = 'simple';
        $item = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 0,
                        'new_qty' => 10,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => false
                ]
            ]
        ];

        $statuses = [
            $productSku => [
                self::STOCK_DEFAULT_ID => true
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        // Add notification and send it
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'Notification should be added to queue');

        // Send notification
        $this->notificationQueueSender->send(false);

        // Verify subscription has send_date set
        $subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
        $subscription = $subscriptionCollection->getFirstItem();
        $this->assertNotNull($subscription->getSendDate(), 'Subscription should have send_date set after notification is sent');

        // Try to add notification again - should be prevented because send_date is set
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(0, $this->notificationCollection->getSize(), 'Notification should not be added for subscription that already received notification');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItPreventsNotificationsAfterMultipleStockUpdates()
    {
        $productSku = 'simple';
        $item = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 0,
                        'new_qty' => 10,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => false
                ]
            ]
        ];

        $statuses = [
            $productSku => [
                self::STOCK_DEFAULT_ID => true
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        // First stock update - should add notification
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'First notification should be added');

        // Second stock update (e.g., from 10 to 15) - should NOT add duplicate
        $itemSecondUpdate = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 10,
                        'new_qty' => 15,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => true
                ]
            ]
        ];

        $this->addNotificationToQueue->execute($itemSecondUpdate);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'Second stock update should not add duplicate notification');

        // Third stock update (e.g., from 15 to 20) - should NOT add duplicate
        $itemThirdUpdate = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 15,
                        'new_qty' => 20,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => true
                ]
            ]
        ];

        $this->addNotificationToQueue->execute($itemThirdUpdate);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'Third stock update should not add duplicate notification');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscription_single_simple.php
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/subscriptions_confirmed_customer.php
     */
    public function testItAllowsNewNotificationAfterPreviousWasSent()
    {
        $productSku = 'simple';
        $item = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 0,
                        'new_qty' => 10,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => false
                ]
            ]
        ];

        $statuses = [
            $productSku => [
                self::STOCK_DEFAULT_ID => true
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);

        // Add and send first notification
        $this->addNotificationToQueue->execute($item);
        $this->notificationQueueSender->send(false);

        // Reset send_date to simulate new subscription cycle (in real scenario this wouldn't happen,
        // but we test that if send_date is cleared, new notification can be added)
        $subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
        $subscription = $subscriptionCollection->getFirstItem();
        $subscription->setSendDate(null);
        $this->backInStockSubscriptionRepository->save($subscription);

        // Clear notification queue
        $notificationRepository = $this->objectManager->create(\MageSuite\BackInStock\Api\NotificationRepositoryInterface::class);
        $notificationCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\Notification\Collection::class);
        foreach ($notificationCollection as $notification) {
            $notificationRepository->delete($notification);
        }

        // Product goes out of stock and back in stock again
        $itemOutOfStock = [
            $productSku => [
                'source_items' => [
                    self::SOURCE_CODE_DEFAULT => [
                        'old_qty' => 10,
                        'new_qty' => 0,
                        'old_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_IN_STOCK,
                        'new_status' => \Magento\InventoryApi\Api\Data\SourceItemInterface::STATUS_OUT_OF_STOCK
                    ]
                ],
                'salable_status_before' => [
                    self::STOCK_DEFAULT_ID => true
                ]
            ]
        ];

        $statusesOutOfStock = [
            $productSku => [
                self::STOCK_DEFAULT_ID => false
            ]
        ];

        $this->getSalableStatusesStub->method('execute')->willReturn($statusesOutOfStock);

        // Product comes back in stock
        $this->getSalableStatusesStub->method('execute')->willReturn($statuses);
        $this->addNotificationToQueue->execute($item);

        $this->notificationCollection->clear();
        $this->assertEquals(1, $this->notificationCollection->getSize(), 'New notification should be added after previous was sent and send_date was cleared');
    }
}
