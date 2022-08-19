<?php

namespace MageSuite\BackInStock\ViewModel;

class Product implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\BackInStock\Model\ProductResolverPool
     */
    protected $productResolverPool;

    public function __construct(
        \Magento\Framework\Registry $registry,
        \MageSuite\BackInStock\Model\ProductResolverPool $productResolverPool
    ) {
        $this->registry = $registry;
        $this->productResolverPool = $productResolverPool;
    }

    public function canRenderBackInStockForm()
    {
        $product = $this->getProduct();

        if (!$product) {
            return false;
        }

        /** @var \MageSuite\BackInStock\Model\ProductResolver\ProductResolverInterface $productResolver */
        $productResolver = $this->productResolverPool->getProductResolver($product->getTypeId());

        if (!$productResolver) {
            return false;
        }

        return $productResolver->canRenderForm($product);
    }

    public function getProductId()
    {
        $product = $this->getProduct();

        if (!$product) {
            return null;
        }

        return $product->getId();
    }

    protected function getProduct()
    {
        $product = $this->registry->registry('current_product');

        if ($product && $product->getId()) {
            return $product;
        }

        return false;
    }
}
