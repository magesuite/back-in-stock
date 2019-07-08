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
    protected $storeManager;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    protected $getSalableQuantityDataBySku;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    public function __construct(
        \MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku $getSalableQuantityDataBySku,
        \Magento\Framework\Registry $registry
    )
    {
        $this->notificationQueueCreator = $notificationQueueCreator;
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->getSalableQuantityDataBySku = $getSalableQuantityDataBySku;
        $this->registry = $registry;
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

        $sku = $this->registry->registry('current_product')->getSku();
        $stockInfo = $this->getSalableQuantityDataBySku->execute($sku);

        if(!isset($stockInfo[0]) || $stockInfo[0]['qty'] < 1){
            return;
        }

        if(!$itemOrigData['is_in_stock'] && $itemData['is_in_stock']){
            foreach ($this->storeManager->getStores() as $store) {
                $this->notificationQueueCreator->addNotificationsToQueue($itemData['product_id'], $store->getId(), 'automatic_notification');
            }
        }
    }
}