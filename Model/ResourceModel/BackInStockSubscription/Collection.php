<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        protected \Magento\Catalog\Model\Product\Visibility $productVisibility,
        protected \MageSuite\BackInStock\Model\Config $config,
        ?\Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        ?\Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $connection, $resource);
    }

    protected function _construct()
    {
        $this->_init(\MageSuite\BackInStock\Model\BackInStockSubscription::class, \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription::class);
    }

    public function addProductsToCollection(): self
    {
        return $this->setFlag('products_assigned', true);
    }

    protected function assignProducts(): self
    {
        if (!$this->getFlag('products_assigned')) {
            return $this;
        }

        $productIds = $this->getColumnValues('product_id');
        $parentProductIds = $this->getColumnValues('parent_product_id');
        $productIds = array_unique(array_filter(array_merge($productIds, $parentProductIds)));

        if (empty($productIds)) {
            return $this;
        }

        $productCollection = $this->productCollectionFactory->create();
        $productCollection->setVisibility($this->productVisibility->getVisibleInSiteIds())
            ->addIdFilter($productIds)
            ->addAttributeToSelect($this->config->getProductAttributes())
            ->addPriceData()
            ->addTaxPercents()
            ->addUrlRewrite();

        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            $item->setProduct($product);
            $parentProduct = $productCollection->getItemById($item->getParentProductId());
            $item->setParentProduct($parentProduct);
        }

        return $this;
    }

    protected function _afterLoad()
    {
        parent::_afterLoad();
        $this->assignProducts();

        return $this;
    }
}
