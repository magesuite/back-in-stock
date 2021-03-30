<?php
namespace MageSuite\BackInStock\Model;

class StockInfo
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Stock
     */
    protected $resourceModel;

    /**
     * @var \Magento\InventorySalesApi\Api\StockResolverInterface
     */
    protected $stockResolver;

    /**
     * store_id => stock_id
     * @var array
     */
    protected $storeIdStockIdMap = [];

    /**
     * source_code => stock_id
     * @var array
     */
    protected $sourceCodeStockIdMap = [];

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\BackInStock\Model\ResourceModel\Stock $resourceModel,
        \Magento\InventorySalesApi\Api\StockResolverInterface $stockResolver
    ) {
        $this->storeManager = $storeManager;
        $this->resourceModel = $resourceModel;
        $this->stockResolver = $stockResolver;
    }

    public function getStoreIdStockIdMap()
    {
        if (empty($this->storeIdStockIdMap)) {
            $this->getStockIdMaps();
        }

        return $this->storeIdStockIdMap;
    }

    public function getSourceCodeStockIdMap()
    {
        if (empty($this->sourceCodeStockIdMap)) {
            $this->getStockIdMaps();
        }

        return $this->sourceCodeStockIdMap;
    }

    protected function getStockIdMaps()
    {
        $storeIdStockIdMap = [];
        $sourceCodeStockIdMap = [];

        $stockIdSourceCodeMap = $this->resourceModel->getStockIdSourceCodeMap();

        foreach ($this->storeManager->getStores() as $store) {
            $websiteCode = $store->getWebsite()->getCode();
            $stock = $this->stockResolver->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = (int) $stock->getStockId();

            $storeIdStockIdMap[$store->getId()] = $stockId;

            if (!isset($stockIdSourceCodeMap[$stockId])) {
                continue;
            }

            foreach ($stockIdSourceCodeMap[$stockId] as $sourceCode) {
                $sourceCodeStockIdMap[$sourceCode][] = $stockId;
            }
        }

        $this->storeIdStockIdMap = $storeIdStockIdMap;
        $this->sourceCodeStockIdMap = $sourceCodeStockIdMap;
    }
}
