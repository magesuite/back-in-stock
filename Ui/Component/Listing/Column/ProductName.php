<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class ProductName extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * @param array $dataSource
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function prepareDataSource(array $dataSource)
    {
        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        $productIds = [];
        foreach ($dataSource['data']['items'] as $item) {
            $productIds[] = $item['entity_id'];
        }
        if (empty($products)) {
            return $dataSource;
        }

        $productCollection = $this->productCollectionFactory->create();
        $products = $productCollection
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->getItems();

        foreach ($dataSource['data']['items'] as &$item) {
            $item['product_name'] = $products[$item['entity_id']]->getName();
        }

        return $dataSource;
    }
}
