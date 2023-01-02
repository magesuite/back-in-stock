<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class ProductName extends \Magento\Ui\Component\Listing\Columns\Column
{
    protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
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
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $productIds = [];

        foreach ($dataSource['data']['items'] as $item) {
            $productIds[] = $item['product_id'];
        }

        if (empty($productIds)) {
            return $dataSource;
        }

        $productCollection = $this->productCollectionFactory->create();
        $products = $productCollection
            ->addAttributeToSelect('entity_id')
            ->addAttributeToSelect('name')
            ->addFieldToFilter('entity_id', ['in' => $productIds])
            ->getItems();

        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($products[$item['product_id']])) {
                continue;
            }

            $item['product_name'] = $products[$item['product_id']]->getName();
        }

        return $dataSource;
    }
}
