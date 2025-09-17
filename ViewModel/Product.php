<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\ViewModel;

class Product implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\ProductResolverPool $productResolverPool,
        protected \Magento\Framework\Registry $registry
    ) {
    }

    public function canRenderBackInStockForm(): bool
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

    public function getProductId(): ?int
    {
        $product = $this->getProduct();

        if (!$product) {
            return null;
        }

        return (int) $product->getId();
    }

    protected function getProduct(): ?\Magento\Catalog\Api\Data\ProductInterface
    {
        $product = $this->registry->registry('current_product');

        if ($product && $product->getId()) {
            return $product;
        }

        return null;
    }
}
