<?php

namespace MageSuite\BackInStock\Model\Command;

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

    public function execute($sourceItems)
    {
        $backInStockItems = $this->getBackInStockItems->execute($sourceItems);

        if (!empty($backInStockItems)) {
            $this->queuePublisher->publish($this->handlerClass, $backInStockItems);
        }
    }
}
