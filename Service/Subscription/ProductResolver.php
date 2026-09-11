<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service\Subscription;

class ProductResolver
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\ResourceModel\Product $productResource,
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
    }

    public function resolve(array $params): \Magento\Catalog\Api\Data\ProductInterface
    {
        $typeId = $this->productResource->getTypeIdByProductId($params['product']);

        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $product = $this->getChildProduct($params, (int)$params['product']);
            $product->setParentProductId($params['product']);

            return $product;
        }

        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            $product = $this->productRepository->getById($params['simple_id']);
            $product->setParentProductId($params['product']);

            return $product;
        }

        return $this->productRepository->getById($params['product']);
    }

    protected function getChildProduct(array $params, int $productId): \Magento\Catalog\Api\Data\ProductInterface
    {
        $product = $this->productRepository->getById($productId);
        $superAttributes = $params['super_attribute'];

        $productCollection = $product->getTypeInstance()
            ->getUsedProductCollection($product)
            ->addAttributeToSelect('name');

        $productCollection->setFlag('has_stock_status_filter');

        foreach ($superAttributes as $attributeId => $attributeValue) {
            $productCollection->addAttributeToFilter($attributeId, $attributeValue);
        }

        $childProduct = $productCollection->getFirstItem();

        if (!$childProduct->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(
                __('Selected product combination is not available.')
            );
        }

        return $childProduct;
    }
}
