<?php
namespace MageSuite\BackInStock\Api\Data;

interface IsProductSalableResultInterface
{
    /**
     * Retrieve previous salable status.
     *
     * @return bool
     */
    public function wasSalable(): bool;

    /**
     * Retrieve is salable result.
     *
     * @return bool
     */
    public function isSalable(): bool;
}
