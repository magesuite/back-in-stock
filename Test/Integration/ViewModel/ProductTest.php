<?php

namespace MageSuite\BackInStock\Test\Integration\ViewModel;

/**
 * @magentoDbIsolation enabled
 * @magentoAppIsolation enabled
 */
class ProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \MageSuite\BackInStock\ViewModel\Product
     */
    protected $viewModel;

    public function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->registry = $objectManager->get(\Magento\Framework\Registry::class);
        $this->viewModel = $objectManager->get(\MageSuite\BackInStock\ViewModel\Product::class);
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     * @magentoDataFixture Magento/Catalog/_files/product_simple_out_of_stock.php
     */
    public function testItReturnsCorrectFlagForSimpleProducts()
    {
        $inStockProduct = $this->productRepository->get('simple');
        $this->registry->register('current_product', $inStockProduct);

        $this->assertFalse($this->viewModel->canRenderBackInStockForm());

        $this->registry->unregister('current_product');

        $outOfStockProduct = $this->productRepository->get('simple-out-of-stock');
        $this->registry->register('current_product', $outOfStockProduct);

        $this->assertTrue($this->viewModel->canRenderBackInStockForm());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/configurable_products.php
     */
    public function testItReturnsCorrectFlagForConfigurableProducts()
    {
        $inStockProduct = $this->productRepository->get('configurable_in_stock');
        $this->registry->register('current_product', $inStockProduct);

        $this->assertFalse($this->viewModel->canRenderBackInStockForm());

        $this->registry->unregister('current_product');

        $outOfStockProduct = $this->productRepository->get('configurable_ouf_of_stock');
        $this->registry->register('current_product', $outOfStockProduct);

        $this->assertTrue($this->viewModel->canRenderBackInStockForm());
    }

    /**
     * @magentoAppArea frontend
     * @magentoDbIsolation enabled
     * @magentoAppIsolation enabled
     * @magentoDataFixture MageSuite_BackInStock::Test/_files/grouped_products.php
     */
    public function testItReturnsCorrectFlagForGroupedProducts()
    {
        $inStockProduct = $this->productRepository->get('grouped_in_stock');
        $this->registry->register('current_product', $inStockProduct);

        $this->assertFalse($this->viewModel->canRenderBackInStockForm());

        $this->registry->unregister('current_product');

        $outOfStockProduct = $this->productRepository->get('grouped_out_of_stock');
        $this->registry->register('current_product', $outOfStockProduct);

        $this->assertTrue($this->viewModel->canRenderBackInStockForm());
    }
}
