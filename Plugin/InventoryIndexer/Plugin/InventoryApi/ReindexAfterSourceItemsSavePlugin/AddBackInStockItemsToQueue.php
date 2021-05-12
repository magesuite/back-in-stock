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
     * @var \MageSuite\BackInStock\Model\Data\SourceItemsToQueueContainer
     */
    protected $sourceItemsToQueueContainer;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\Queue\Service\Publisher $queuePublisher,
        \MageSuite\BackInStock\Model\Data\SourceItemsToQueueContainer $sourceItemsToQueueContainer
    ) {
        $this->configuration = $configuration;
        $this->queuePublisher = $queuePublisher;
        $this->sourceItemsToQueueContainer = $sourceItemsToQueueContainer;
    }

    public function afterAfterExecute(\Magento\InventoryIndexer\Plugin\InventoryApi\ReindexAfterSourceItemsSavePlugin $subject, $result)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return $result;
        }

        $backInStockItems = $this->sourceItemsToQueueContainer->getItems();

        if(empty($backInStockItems)) {
            return $result;
        }

        $this->queuePublisher->publish($this->handlerClass, $backInStockItems);
        $this->sourceItemsToQueueContainer->clearItems();

        return $result;
    }
}
