<?php

namespace MageSuite\BackInStock\Model\Data;

/**
 * Back in stock items need to be collected before source items are saved, however, they should be added
 * to the queue when source items are saved and stock in indexed.
 *
 * \MageSuite\BackInStock\Plugin\Inventory\Model\SourceItem\Command\SourceItemsSave\CollectBackInStockItemsForQueue
 * \MageSuite\BackInStock\Plugin\Inventory\Model\SourceItem\Command\SourceItemsSaveWithoutLegacySynchronization\CollectBackInStockItemsForQueue
 * plugins add items to container
 *
 * \MageSuite\BackInStock\Plugin\InventoryIndexer\Plugin\InventoryApi\ReindexAfterSourceItemsSavePlugin\AddBackInStockItemsToQueue
 * plugin adds items from container to queue
 */
class SourceItemsToQueueContainer
{
    /**
     * @var array
     */
    protected $items = [];

    public function addItems($items)
    {
        if (!empty($this->items)) {
            $items = array_merge($items, $this->items);
        }

        $this->items = $items;
    }

    public function getItems()
    {
        return $this->items;
    }

    public function clearItems()
    {
        $this->items = [];
    }
}
