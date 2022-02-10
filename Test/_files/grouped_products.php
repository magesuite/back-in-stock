<?php
$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);

$inStockProduct = $objectManager->create(\Magento\Catalog\Model\Product::class);
$inStockProduct->isObjectNew(true);
$inStockProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple in stock')
    ->setSku('simple_in_stock')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$inStockProduct = $productRepository->save($inStockProduct);

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Grouped Product in stock')
    ->setSku('grouped_in_stock')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

$newLinks = [];
$productLinkFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);

$productLink = $productLinkFactory->create();
$productLink
    ->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($inStockProduct->getSku())
    ->setLinkedProductType($inStockProduct->getTypeId())
    ->setPosition(1)
    ->getExtensionAttributes()
    ->setQty(1);
$newLinks[] = $productLink;

$product->setProductLinks($newLinks);
$product->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);

$outOfStockProduct = $objectManager->create(\Magento\Catalog\Model\Product::class);
$outOfStockProduct->isObjectNew(true);
$outOfStockProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Simple out of stock')
    ->setSku('simple_out_of_stock')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0]);

$outOfStockProduct = $productRepository->save($outOfStockProduct);

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->isObjectNew(true);
$product->setTypeId(\Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE)
    ->setAttributeSetId(4)
    ->setWebsiteIds([1])
    ->setName('Grouped Product out of stock')
    ->setSku('grouped_out_of_stock')
    ->setPrice(100)
    ->setTaxClassId(0)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);

$newLinks = [];
$productLinkFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductLinkInterfaceFactory::class);

$productLink = $productLinkFactory->create();
$productLink
    ->setSku($product->getSku())
    ->setLinkType('associated')
    ->setLinkedProductSku($outOfStockProduct->getSku())
    ->setLinkedProductType($outOfStockProduct->getTypeId())
    ->setPosition(1)
    ->getExtensionAttributes()
    ->setQty(1);
$newLinks[] = $productLink;

$product->setProductLinks($newLinks);
$product->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($product);
