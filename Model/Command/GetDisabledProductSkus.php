<?php

namespace MageSuite\BackInStock\Model\Command;

class GetDisabledProductSkus
{
    protected \Magento\Framework\DB\Adapter\AdapterInterface $connection;

    protected array $disabledProductSkus = [];

    public function __construct(\Magento\Framework\App\ResourceConnection $resourceConnection)
    {
        $this->connection = $resourceConnection->getConnection();
    }

    public function execute(array $skus): array
    {
        if (!empty($this->disabledProductSkus)) {
            return $this->disabledProductSkus;
        }

        $select = $this->connection
            ->select()
            ->from(['cpe' => $this->connection->getTableName('catalog_product_entity')], ['cpe.sku'])
            ->joinLeft(
                ['cpei' => $this->connection->getTableName('catalog_product_entity_int')],
                'cpei.entity_id = cpe.entity_id',
                []
            )
            ->joinLeft(
                ['ea' => $this->connection->getTableName('eav_attribute')],
                'ea.attribute_id = cpei.attribute_id',
                []
            )
            ->where('cpe.sku IN (?)', $skus)
            ->where('ea.attribute_code = "status"')
            ->where('cpei.value = ?', \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);

        $this->disabledProductSkus = $this->connection->fetchCol($select);

        return $this->disabledProductSkus;
    }
}
