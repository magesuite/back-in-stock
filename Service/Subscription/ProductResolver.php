<?php

namespace MageSuite\BackInStock\Service\Subscription;

class ProductResolver
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(\Magento\Catalog\Api\ProductRepositoryInterface $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function resolve($params)
    {
        $product = $this->productRepository->getById($params['product']);

        if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            $product = $this->getChildProduct($params, $product);
            $product->setParentProductId($params['product']);
        }

        return $product;
    }

    /**
     * @param $params
     * @param $product
     * @return mixed
     */
    protected function getChildProduct($params, $product)
    {
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
