<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class CustomerType extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['customer_id'] = $item['customer_id'] > 0 ? __('Registered Customer') : __('Guest');
            }
        }

        return $dataSource;
    }
}
