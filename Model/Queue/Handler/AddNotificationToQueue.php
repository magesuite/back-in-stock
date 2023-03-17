<?php

namespace MageSuite\BackInStock\Model\Queue\Handler;

class AddNotificationToQueue implements \MageSuite\Queue\Api\Queue\HandlerInterface
{
    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription
     */
    protected $subscriptionResourceModel;

    /**
     * @var \MageSuite\BackInStock\Model\StockInfo
     */
    protected $stockInfo;

    /**
     * @var \MageSuite\BackInStock\Api\AreProductsSalableInterface
     */
    protected $areProductsSalable;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification
     */
    protected $notificationResourceModel;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription $subscriptionResourceModel,
        \MageSuite\BackInStock\Model\StockInfo $stockInfo,
        \MageSuite\BackInStock\Api\AreProductsSalableInterface $areProductsSalable,
        \MageSuite\BackInStock\Model\ResourceModel\Notification $notificationResourceModel
    ) {
        $this->subscriptionResourceModel = $subscriptionResourceModel;
        $this->stockInfo = $stockInfo;
        $this->areProductsSalable = $areProductsSalable;
        $this->notificationResourceModel = $notificationResourceModel;
    }

    public function execute($items)
    {
        $groupedItems = $this->groupItemsByStockId($items);

        if (empty($groupedItems)) {
            return;
        }

        $notificationToInsert = $this->getNotificationToInsert($groupedItems);

        if (empty($notificationToInsert)) {
            return;
        }

        $this->notificationResourceModel->insertMultipleNotifications($notificationToInsert);
    }

    protected function getNotificationToInsert($items)
    {
        $notificationToInsert = [];

        $subscriptions = $this->getSubscriptions($items);

        foreach ($subscriptions as $subscription) {
            $notificationToInsert[] = $this->notificationItemBuilder($subscription);
        }

        return $notificationToInsert;
    }

    protected function getSubscriptions($items)
    {
        $result = [];

        $subscriptions = $this->subscriptionResourceModel->getSubscriptionsBySkus(array_keys($items));

        $storeIdStockIdMap = $this->stockInfo->getStoreIdStockIdMap();
        $areProductsSalable = $this->areProductsSalable->execute($this->getSkus($subscriptions), $items);

        foreach ($subscriptions as $subscription) {
            $stockId = $storeIdStockIdMap[$subscription['store_id']] ?? null;
            $isProductSalableItem = $areProductsSalable[$subscription['sku']][$stockId] ?? null;

            if (!$stockId || !$isProductSalableItem) {
                continue;
            }

            if ($isProductSalableItem->wasSalable() || !$isProductSalableItem->isSalable()) {
                continue;
            }

            $result[] = $subscription;
        }

        return $result;
    }

    protected function notificationItemBuilder($subscription)
    {
        return [
            \MageSuite\BackInStock\Api\Data\NotificationInterface::SUBSCRIPTION_ID => $subscription['id'],
            \MageSuite\BackInStock\Api\Data\NotificationInterface::NOTIFICATION_TYPE => \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION,
            \MageSuite\BackInStock\Api\Data\NotificationInterface::MESSAGE => ''
        ];
    }

    /*
     * Group items by sku and stock Id
     *
     * Data added to the MageSuite queue is grouped by source_code.
     * We need to group data by stock_id to validate stock status
     */
    protected function groupItemsByStockId($items)
    {
        $preparedItems = [];
        $sourceCodeStockIdMap = $this->stockInfo->getSourceCodeStockIdMap();

        foreach ($items as $sku => $item) {
            foreach ($item as $sourceCode => $itemInfo) {
                $stockIds = $sourceCodeStockIdMap[$sourceCode] ?? null;

                if (!$stockIds) {
                    continue;
                }

                foreach ($stockIds as $stockId) {
                    $oldQty = $preparedItems[$sku][$stockId]['old_qty'] ?? 0;
                    $newQty = $preparedItems[$sku][$stockId]['new_qty'] ?? 0;
                    $oldStatus = $preparedItems[$sku][$stockId]['old_status'] ?? true;

                    $preparedItems[$sku][$stockId] = [
                        'old_qty' => $oldQty + $itemInfo['old_qty'],
                        'new_qty' => $newQty + $itemInfo['new_qty'],
                        'old_status' => !$oldStatus ? $oldStatus : $itemInfo['old_status']
                    ];
                }
            }
        }

        return $preparedItems;
    }

    protected function getSkus($subscriptions)
    {
        $skus = array_column($subscriptions, 'sku');

        return array_unique($skus);
    }
}
