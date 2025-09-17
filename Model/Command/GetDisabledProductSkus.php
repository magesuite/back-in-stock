<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model\Command;

class GetDisabledProductSkus
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;
    protected array $disabledProductSkus = [];

    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        protected \Magento\Framework\EntityManager\MetadataPool $metadataPool
    ) {
        $this->connection = $resourceConnection->getConnection();
    }

    public function execute(array $skus, int $storeId): array
    {
        if (!empty($this->disabledProductSkus[$storeId])) {
            return $this->disabledProductSkus[$storeId];
        }

        $linkField = $this->metadataPool
            ->getMetadata(\Magento\Catalog\Api\Data\ProductInterface::class)
            ->getLinkField();

        $select = $this->connection
            ->select()
            ->from(['cpe' => $this->connection->getTableName('catalog_product_entity')], ['cpe.sku'])
            ->joinLeft(
                ['cpei' => $this->connection->getTableName('catalog_product_entity_int')],
                sprintf('cpei.%s = cpe.%s', $linkField, $linkField),
                []
            )
            ->joinLeft(
                ['ea' => $this->connection->getTableName('eav_attribute')],
                'ea.attribute_id = cpei.attribute_id',
                []
            )
            ->where('cpe.sku IN (?)', $skus)
            ->where('ea.attribute_code = ?', 'status')
            ->where('cpei.value = ?', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED)
            ->where('cpei.store_id = ?', $storeId);

        $this->disabledProductSkus[$storeId] = $this->connection->fetchCol($select);

        return $this->disabledProductSkus[$storeId];
    }
}
