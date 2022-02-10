<?php

namespace MageSuite\BackInStock\Model\ProductResolver;

class Configurable implements ProductResolverInterface
{
    const STOCK_FILTER_FLAG = 'has_stock_status_filter';

    /**
     * @var \Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory
     */
    protected $stockStatusFactory;

    public function __construct(\Magento\CatalogInventory\Model\ResourceModel\Stock\StatusFactory $stockStatusFactory)
    {
        $this->stockStatusFactory = $stockStatusFactory;
    }

    public function isApplicable($productTypeId)
    {
        return $productTypeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
    }

    public function canRenderForm($product)
    {
        $simpleProductCollection = $product->getTypeInstance()->getUsedProductCollection($product);

        if (!$simpleProductCollection->hasFlag(self::STOCK_FILTER_FLAG)) {
            $stockStatusResource = $this->stockStatusFactory->create();
            $stockStatusResource->addStockDataToCollection($simpleProductCollection, false);
            $simpleProductCollection->setFlag(self::STOCK_FILTER_FLAG, true);
        }

        foreach ($simpleProductCollection as $simpleProduct) {
            if (!$simpleProduct->isSaleable()) {
                return true;
            }
        }

        return false;
    }
}
