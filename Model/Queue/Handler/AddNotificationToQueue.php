<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\Queue\Handler;

class AddNotificationToQueue implements \MageSuite\Queue\Api\Queue\HandlerInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription $subscriptionResourceModel,
        protected \MageSuite\BackInStock\Model\StockInfo $stockInfo,
        protected \MageSuite\BackInStock\Api\AreProductsSalableInterface $areProductsSalable,
        protected \MageSuite\BackInStock\Model\ResourceModel\Notification $notificationResourceModel,
        protected \MageSuite\BackInStock\Model\Command\GetDisabledProductSkus $getDisabledProductSkus
    ) {
    }

    public function execute($items) //phpcs:ignore
    {
        if (empty($items)) {
            return $this;
        }

        $notificationToInsert = $this->getNotificationToInsert($items);

        if (empty($notificationToInsert)) {
            return $this;
        }

        $this->notificationResourceModel->insertMultipleNotifications($notificationToInsert);

        return $this;
    }

    protected function getNotificationToInsert(array $items): array
    {
        $notificationToInsert = [];
        $subscriptions = $this->getSubscriptions($items);

        foreach ($subscriptions as $subscription) {
            $notificationToInsert[] = $this->notificationItemBuilder($subscription);
        }

        return $notificationToInsert;
    }

    protected function getSubscriptions(array $items): array
    {
        $result = [];
        $subscriptions = $this->subscriptionResourceModel->getSubscriptionsBySkus(array_keys($items));
        $skus = $this->getSkus($subscriptions);
        $storeIdStockIdMap = $this->stockInfo->getStoreIdStockIdMap();
        $areProductsSalable = $this->areProductsSalable->execute($skus, $items);

        foreach ($subscriptions as $subscription) {
            $subscriptionStoreId = (int) $subscription['store_id'];
            $disabledProductSkus = $this->getDisabledProductSkus->execute($skus, $subscriptionStoreId);

            if (in_array($subscription['sku'], $disabledProductSkus)) {
                continue;
            }

            $stockId = $storeIdStockIdMap[$subscriptionStoreId] ?? null;
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

    protected function notificationItemBuilder(array $subscription): array
    {
        return [
            \MageSuite\BackInStock\Api\Data\NotificationInterface::SUBSCRIPTION_ID => $subscription['id'],
            \MageSuite\BackInStock\Api\Data\NotificationInterface::NOTIFICATION_TYPE => \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION,
            \MageSuite\BackInStock\Api\Data\NotificationInterface::MESSAGE => ''
        ];
    }

    /**
     * Group items by sku and stock ID
     *
     * Data added to the MageSuite queue is grouped by source_code.
     * We need to group data by stock_id to validate stock status
     */
    protected function groupItemsByStockId(array $items): array
    {
        $preparedItems = [];
        $sourceCodeStockIdMap = $this->stockInfo->getSourceCodeStockIdMap();

        foreach ($items as $sku => $item) {
            foreach ($item['source_items'] as $sourceCode => $itemInfo) {
                $stockIds = $sourceCodeStockIdMap[$sourceCode] ?? null;

                if (!$stockIds) {
                    continue;
                }

                foreach ($stockIds as $stockId) {
                    $salableStatusBefore = $items['salable_status_before'][$sku][$stockId] ?? null;

                    if (!$salableStatusBefore) { //phpcs:ignore
                        continue;
                    }

                    $preparedItems[$sku][$stockId] = [
                        'salable_status_before' => $salableStatusBefore,
                    ];
                }
            }
        }

        return $preparedItems;
    }

    protected function getSkus(array $subscriptions): array
    {
        $skus = array_column($subscriptions, 'sku');

        return array_unique($skus);
    }
}
