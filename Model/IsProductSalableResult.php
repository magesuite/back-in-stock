<?php

namespace MageSuite\BackInStock\Model;

class IsProductSalableResult implements \MageSuite\BackInStock\Api\Data\IsProductSalableResultInterface
{
    /**
     * @var bool
     */
    protected $wasSalable;

    /**
     * @var bool
     */
    protected $isSalable;

    public function __construct(
        bool $wasSalable,
        bool $isSalable
    ) {
        $this->wasSalable = $wasSalable;
        $this->isSalable = $isSalable;
    }

    public function wasSalable(): bool
    {
        return $this->wasSalable;
    }

    public function isSalable(): bool
    {
        return $this->isSalable;
    }
}
