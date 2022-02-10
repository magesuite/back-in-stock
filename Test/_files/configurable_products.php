<?php
$resolver = \Magento\TestFramework\Workaround\Override\Fixture\Resolver::getInstance();
$resolver->requireDataFixture('Magento/ConfigurableProduct/_files/configurable_attribute.php');

$objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

$productRepository = $objectManager->get(\Magento\Catalog\Api\ProductRepositoryInterface::class);
$productFactory = $objectManager->get(\Magento\Catalog\Model\ProductFactory::class);
$optionsFactory = $objectManager->get(\Magento\ConfigurableProduct\Helper\Product\Options\Factory::class);
$productExtensionAttributesFactory = $objectManager->get(\Magento\Catalog\Api\Data\ProductExtensionInterfaceFactory::class);
$eavConfig = $objectManager->get(\Magento\Eav\Model\Config::class);

$attribute = $eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'test_configurable');
$option = $attribute->getSource()->getOptionId('Option 1');

$configurableOptions = $optionsFactory->create(
    [
        [
            'attribute_id' => $attribute->getId(),
            'code' => $attribute->getAttributeCode(),
            'label' => $attribute->getStoreLabel(),
            'position' => '0',
            'values' => [['label' => 'test', 'attribute_id' => $attribute->getId(), 'value_index' => $option]],
        ],
    ]
);

$inStockProduct = $productFactory->create();
$inStockProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($inStockProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Configurable Option 1')
    ->setSku('simple_in_stock')
    ->setPrice(10.00)
    ->setTestConfigurable($option)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 100, 'is_qty_decimal' => 0, 'is_in_stock' => 1]);

$inStockProduct = $productRepository->save($inStockProduct);

$outOfStockProduct = $productFactory->create();
$outOfStockProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE)
    ->setAttributeSetId($outOfStockProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Configurable Option 1')
    ->setSku('simple_out_of_stock')
    ->setPrice(10.00)
    ->setTestConfigurable($option)
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'qty' => 0, 'is_qty_decimal' => 0, 'is_in_stock' => 0]);

$outOfStockProduct = $productRepository->save($outOfStockProduct);


$extensionConfigurableAttributes = $inStockProduct->getExtensionAttributes() ?: $productExtensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks([$inStockProduct->getId()]);

$configurableProduct = $productFactory->create();
$configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);
$configurableProduct->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAttributeSetId($configurableProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Configurable Product In Stock')
    ->setSku('configurable_in_stock')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($configurableProduct);

$extensionConfigurableAttributes = $outOfStockProduct->getExtensionAttributes() ?: $productExtensionAttributesFactory->create();
$extensionConfigurableAttributes->setConfigurableProductOptions($configurableOptions);
$extensionConfigurableAttributes->setConfigurableProductLinks([$outOfStockProduct->getId()]);

$configurableProduct = $productFactory->create();
$configurableProduct->setExtensionAttributes($extensionConfigurableAttributes);
$configurableProduct->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
    ->setAttributeSetId($configurableProduct->getDefaultAttributeSetId())
    ->setWebsiteIds([1])
    ->setName('Configurable Product Out of Stock')
    ->setSku('configurable_ouf_of_stock')
    ->setVisibility(\Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
    ->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
    ->setStockData(['use_config_manage_stock' => 1, 'is_in_stock' => 1]);
$productRepository->save($configurableProduct);
