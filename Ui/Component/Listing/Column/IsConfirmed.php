<?php
namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class IsConfirmed extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['customer_confirmed'] = $item['customer_confirmed'] ? __('Yes') : __('No');
            }
        }

        return $dataSource;
    }
}
