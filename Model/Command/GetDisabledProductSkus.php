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

        $select = $this->connection
            ->select()
            ->from(['cpe' => $this->connection->getTableName('catalog_product_entity')], ['cpe.sku'])
            ->joinLeft(
                ['cpei_default' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_default.%1$s = cpe.%1$s AND cpei_default.attribute_id = %2$d AND cpei_default.store_id = 0',
                    $linkField, $attributeId
                ), []
            )
            ->joinLeft(
                ['cpei_store' => $attribute->getBackendTable()],
                sprintf(
                    'cpei_store.%1$s = cpe.%1$s AND cpei_store.attribute_id = %2$d AND cpei_store.store_id = %3$d',
                    $linkField, $attributeId, (int)$storeId
                ), []
            )
            ->where('cpe.sku IN (?)', $skus)
            ->where(sprintf('COALESCE(cpei_store.value, cpei_default.value) = %d', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED));

        $this->disabledProductSkus[$storeId] = $this->connection->fetchCol($select);

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
}
