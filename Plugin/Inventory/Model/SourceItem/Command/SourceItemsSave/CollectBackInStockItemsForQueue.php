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
     * @var \MageSuite\BackInStock\Model\Data\ItemsToQueueContainer
     */
    protected $itemsToQueueContainer;

    public function __construct(
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\BackInStock\Model\Command\GetBackInStockItems $getBackInStockItems,
        \MageSuite\BackInStock\Model\Data\ItemsToQueueContainer $itemsToQueueContainer
    ) {
        $this->configuration = $configuration;
        $this->getBackInStockItems = $getBackInStockItems;
        $this->itemsToQueueContainer = $itemsToQueueContainer;
    }

    public function aroundExecute(\Magento\Inventory\Model\SourceItem\Command\SourceItemsSave $subject, callable $proceed, $sourceItems)
    {
        if (!$this->configuration->isModuleEnabled()) {
            return $proceed($sourceItems);
        }

        $backInStockItems = $this->getBackInStockItems->execute($sourceItems);

        if (!empty($backInStockItems)) {
            $this->itemsToQueueContainer->setItems($backInStockItems);
        }

        return $proceed($sourceItems);
    }
}
