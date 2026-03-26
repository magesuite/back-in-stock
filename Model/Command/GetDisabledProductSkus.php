<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\Command;

class GetDisabledProductSkus
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected array $disabledProductSkus = [];
    protected ?bool $isStatusAttributeGlobal = null;

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Eav\Model\Config $eavConfig,
        protected \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->connection = $resourceConnection->getConnection();
    }

    public function execute(array $skus, int $storeId): array
    {
        $storeId = $this->isStatusAttributeGlobal() ? 0 : $storeId;

        if (!empty($this->disabledProductSkus[$storeId])) {
            return $this->disabledProductSkus[$storeId];
        }

        $linkField = $this->metadataPool
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');
        $attributeId = (int)$attribute->getAttributeId();

        $disabledStatus = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;

        $select = $this->connection
            ->select()
            ->from(['cpe' => $this->connection->getTableName('catalog_product_entity')], ['cpe.sku'])
            ->join(
                ['cpei_default' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_default.%1$s = cpe.%1$s AND cpei_default.attribute_id = %2$d AND cpei_default.store_id = 0',
                    $linkField, $attributeId
                ), []
            )
            ->join(
                ['cpei_store' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_store.%1$s = cpe.%1$s AND cpei_store.attribute_id = %2$d AND cpei_store.store_id = %3$d',
                    $linkField, $attributeId, (int)$storeId
                ), []
            )
            ->where('cpe.sku IN (?)', $skus)
            ->where(sprintf('COALESCE(cpei_store.value, cpei_default.value) = %d', $disabledStatus));

        $selectParentDisabled = $this->connection
            ->select()
            ->from(['cpe' => $this->connection->getTableName('catalog_product_entity')], ['cpe.sku'])
            ->join(
                ['cpsl' => $this->connection->getTableName('catalog_product_super_link')],
                'cpsl.product_id = cpe.entity_id',
                []
            )
            ->join(
                ['cpe_parent' => $this->connection->getTableName('catalog_product_entity')],
                sprintf('cpe_parent.%s = cpsl.parent_id', $linkField),
                []
            )
            ->join(
                ['cpei_parent_default' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_parent_default.%1$s = cpe_parent.%1$s AND cpei_parent_default.attribute_id = %2$d AND cpei_parent_default.store_id = 0',
                    $linkField, $attributeId
                ), []
            )
            ->join(
                ['cpei_parent_store' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_parent_store.%1$s = cpe_parent.%1$s AND cpei_parent_store.attribute_id = %2$d AND cpei_parent_store.store_id = %3$d',
                    $linkField, $attributeId, (int)$storeId
                ), []
            )
            ->where('cpe.sku IN (?)', $skus)
            ->where(sprintf('COALESCE(cpei_parent_store.value, cpei_parent_default.value) = %d', $disabledStatus));

        $unionSelect = $this->connection->select()->union([$select, $selectParentDisabled]);

        $this->disabledProductSkus[$storeId] = $this->connection->fetchCol($unionSelect);

        return $this->disabledProductSkus[$storeId];
    }

    protected function isStatusAttributeGlobal(): bool
    {
        if (is_bool($this->isStatusAttributeGlobal)) {
            return $this->isStatusAttributeGlobal;
        }

        $attribute = $this->eavConfig->getAttribute(\Magento\Catalog\Model\Product::ENTITY, 'status');
        $this->isStatusAttributeGlobal = (int)$attribute->getIsGlobal() === \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_GLOBAL;

        return $this->isStatusAttributeGlobal;
    }

    public function flushDisabledSkusCache(): void
    {
        unset($this->disabledProductSkus);
    }
}
