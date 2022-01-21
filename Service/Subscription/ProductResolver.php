<?php

namespace MageSuite\BackInStock\Service\Subscription;

class ProductResolver
{
    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Product
     */
    protected $productResource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\Product $productResource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->productResource = $productResource;
        $this->productRepository = $productRepository;
    }

    public function resolve($params)
    {
        $typeId = $this->productResource->getTypeIdByProductId($params['product']);

        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $product = $this->getChildProduct($params, $params['product']);
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

    /**
     * @param array $params
     * @param int $productId
     * @return mixed
     */
    protected function getChildProduct($params, $productId)
    {
        $product = $this->productRepository->getById($productId);
        $superAttributes = $params["super_attribute"];

        $productCollection = $product->getTypeInstance()
            ->getUsedProductCollection($product)
            ->addAttributeToSelect('name');

        $productCollection->setFlag('has_stock_status_filter');

        foreach ($superAttributes as $attributeId => $attributeValue) {
            $productCollection->addAttributeToFilter($attributeId, $attributeValue);
        }
        /** @var \Magento\Catalog\Model\Product $productObject */
        $childProduct = $productCollection->getFirstItem();

        return $childProduct;
    }
}
