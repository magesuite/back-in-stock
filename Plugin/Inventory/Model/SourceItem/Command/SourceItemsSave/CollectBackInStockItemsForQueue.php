<?php

namespace MageSuite\BackInStock\Plugin\Inventory\Model\SourceItem\Command\SourceItemsSave;

class CollectBackInStockItemsForQueue
{
    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\BackInStock\Model\Command\GetBackInStockItems
     */
    protected $getBackInStockItems;

    /**
     * @var \MageSuite\BackInStock\Model\Data\SourceItemsToQueueContainer
     */
    protected $sourceItemsToQueueContainer;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\BackInStock\Model\Command\GetBackInStockItems $getBackInStockItems,
        \MageSuite\BackInStock\Model\Data\SourceItemsToQueueContainer $sourceItemsToQueueContainer
    ) {
        $this->configuration = $configuration;
        $this->getBackInStockItems = $getBackInStockItems;
        $this->sourceItemsToQueueContainer = $sourceItemsToQueueContainer;
    }

    public function aroundExecute(\Magento\Inventory\Model\SourceItem\Command\SourceItemsSave $subject, callable $proceed, $sourceItems)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return $proceed($sourceItems);
        }

        $backInStockItems = $this->getBackInStockItems->execute($sourceItems);

        if (!empty($backInStockItems)) {
            $this->sourceItemsToQueueContainer->addItems($backInStockItems);
        }

        return $proceed($sourceItems);
    }
}
