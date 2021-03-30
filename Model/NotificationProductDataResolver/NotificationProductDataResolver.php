<?php

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class NotificationProductDataResolver
{
    const PRODUCT_IMAGE_ID = 'product_page_image_small';

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Helper\Image $imageHelper
    ) {
        $this->productRepository = $productRepository;
        $this->imageHelper = $imageHelper;
    }

    public function getProduct($productId, $storeId)
    {
        try {
            return $this->productRepository->getById($productId, false, $storeId);
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getProductImageUrl($product, $imageId = self::PRODUCT_IMAGE_ID)
    {
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }
}
