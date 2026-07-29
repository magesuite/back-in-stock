<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class Config
{
    public function __construct(
        protected \Magento\Catalog\Model\Config $catalogConfig,
        protected array $attributeList = []
    ) {}

    public function getProductAttributes(): array
    {
        $catalogAttributes = $this->catalogConfig->getProductAttributes();

        return array_merge($catalogAttributes, $this->attributeList);
    }
}
