<?php
namespace MageSuite\BackInStock\Observer;

class AddNotificationsToQueueAfterStockUpdate implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueCreator
     */
    protected $notificationQueueCreator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    private $configuration;

    public function __construct(
        \MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    )
    {
        $this->notificationQueueCreator = $notificationQueueCreator;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if(!$this->configuration->isModuleEnabled()){
            return;
        }

        $itemData = $observer->getItem()->getData();
        $itemOrigData = $observer->getItem()->getOrigData();

        if(!$itemOrigData['is_in_stock'] && $itemData['is_in_stock']){
            foreach ($this->storeManager->getStores() as $store) {
                $this->notificationQueueCreator->addNotificationsToQueue($itemData['product_id'], $store->getId(), 'automatic_notification');
            }
        }
    }
}