<?php

namespace MageSuite\BackInStock\Block\Adminhtml\Notification;

class Form extends \Magento\Backend\Block\Template
{
    protected $_template = 'MageSuite_BackInStock::form.phtml'; //phpcs:ignore

    protected $product = null;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;
    }

    public function getProduct()
    {
        if (!$this->product) {
            $productId = $this->getRequest()->getParam('product_id');

            $this->product = $this->productRepository->getById($productId);
        }

        return $this->product;
    }

    public function getStores()
    {
        $storeId = $this->getRequest()->getParam('store');

        $stores = $storeId ? [$this->storeManager->getStore($storeId)] : $this->storeManager->getStores();

        $storesData = [];

        foreach ($stores as $store) {
            $storesData[$store->getId()] = [
                'store_id' => $store->getId(),
                'name' => $store->getName(),
                'code' => $store->getCode()
            ];
        }

        return $storesData;
    }
}
