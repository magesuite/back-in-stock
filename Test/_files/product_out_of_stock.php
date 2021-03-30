<?php

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$product = $objectManager->create(\Magento\Catalog\Model\Product::class);
$product->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setId(1221)
    ->setAttributeSetId(4)
    ->setName('Product out of stock')
    ->setSku('product_out_of_stock')
    ->setPrice(100)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setWebsiteIds([1])
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0])
    ->setCanSaveCustomOptions(true)
    ->save();


$sourceItem = $objectManager->create(\Magento\InventoryApi\Api\Data\SourceItemInterface::class);
$sourceItem->setSourceCode('default');
$sourceItem->setSku('product_out_of_stock');
$sourceItem->setStatus(0);
$sourceItem->setQuantity((float)0);
$sourceItem->save();
