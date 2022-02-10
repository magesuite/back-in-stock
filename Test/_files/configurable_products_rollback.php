<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$registry = $objectManager->get(\Magento\Framework\Registry::class);

$registry->unregister('isSecureArea');
$registry->register('isSecureArea', true);

$productSkus = ['configurable_in_stock', 'configurable_ouf_of_stock', 'simple_in_stock', 'simple_out_of_stock'];

foreach ($productSkus as $productSku) {
    try {
        $product = $productRepository->get($productSku);
        $product->delete();
    } catch (\Exception $e) {
        //do nothing
    }
}
