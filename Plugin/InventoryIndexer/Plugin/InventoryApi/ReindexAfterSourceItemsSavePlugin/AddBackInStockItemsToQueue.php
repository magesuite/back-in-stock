<?php

namespace MageSuite\BackInStock\Plugin\InventoryIndexer\Plugin\InventoryApi\ReindexAfterSourceItemsSavePlugin;

class AddBackInStockItemsToQueue
{
    protected $handlerClass = \MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue::class;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\Queue\Service\Publisher
     */
    protected $queuePublisher;

    /**
     * @var \MageSuite\BackInStock\Model\Data\ItemsToQueueContainer
     */
    protected $itemsToQueueContainer;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\Queue\Service\Publisher $queuePublisher,
        \MageSuite\BackInStock\Model\Data\ItemsToQueueContainer $itemsToQueueContainer
    ) {
        $this->configuration = $configuration;
        $this->queuePublisher = $queuePublisher;
        $this->itemsToQueueContainer = $itemsToQueueContainer;
    }

    public function afterAfterExecute(\Magento\InventoryIndexer\Plugin\InventoryApi\ReindexAfterSourceItemsSavePlugin $subject, $result)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return $result;
        }

        $backInStockItems = $this->itemsToQueueContainer->getItems();

        if (!empty($backInStockItems)) {
            $this->queuePublisher->publish($this->handlerClass, $backInStockItems);
            $this->itemsToQueueContainer->clearItems();
        }

        return $result;
    }
}
