<?php

namespace MageSuite\BackInStock\Plugin\Inventory\Model\SourceItem\Command\SourceItemsSaveWithoutLegacySynchronization;

class AddBackInStockItemsToQueue
{
    protected $handlerClass = \MageSuite\BackInStock\Model\Queue\Handler\AddNotificationToQueue::class;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\BackInStock\Model\Command\AddBackInStockItemsToQueue
     */
    protected $addBackInStockItemsToQueue;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\BackInStock\Model\Command\AddBackInStockItemsToQueue $addBackInStockItemsToQueue
    ) {
        $this->configuration = $configuration;
        $this->addBackInStockItemsToQueue = $addBackInStockItemsToQueue;
    }

    public function beforeExecute(\Magento\Inventory\Model\SourceItem\Command\SourceItemsSaveWithoutLegacySynchronization $subject, $sourceItems)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return [$sourceItems];
        }

        $this->addBackInStockItemsToQueue->execute($sourceItems);

        return [$sourceItems];
    }
}
