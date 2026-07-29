<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\NotificationProductDataResolver;

class NotificationProductDataResolver
{
    protected const PRODUCT_IMAGE_ID = 'product_page_image_small';

    public function __construct(
        protected \Magento\Catalog\Helper\Image $imageHelper
    ) {}

    public function getProductImageUrl(
        \Magento\Catalog\Api\Data\ProductInterface $product,
        string $imageId = self::PRODUCT_IMAGE_ID
    ): string {
        return $this->imageHelper->init($product, $imageId)->getUrl();
    }
}
