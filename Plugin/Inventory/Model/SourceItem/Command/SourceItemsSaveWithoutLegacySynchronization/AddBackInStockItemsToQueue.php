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
     * @var \MageSuite\BackInStock\Model\Command\GetBackInStockItems
     */
    protected $getBackInStockItems;

    /**
     * @var \MageSuite\Queue\Service\Publisher
     */
    protected $queuePublisher;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\BackInStock\Model\Command\GetBackInStockItems $getBackInStockItems,
        \MageSuite\Queue\Service\Publisher $queuePublisher
    ) {
        $this->configuration = $configuration;
        $this->getBackInStockItems = $getBackInStockItems;
        $this->queuePublisher = $queuePublisher;
    }

    public function aroundExecute(\Magento\Inventory\Model\SourceItem\Command\SourceItemsSaveWithoutLegacySynchronization $subject, callable $proceed, $sourceItems)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return $proceed($sourceItems);
        }

        $backInStockItems = $this->getBackInStockItems->execute($sourceItems);

        $proceed($sourceItems);

        if (!empty($backInStockItems)) {
            $this->queuePublisher->publish($this->handlerClass, $backInStockItems);
        }

        return true;
    }
}
