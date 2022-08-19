<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class NotificationChannel extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['notification_channel'] = ucfirst($item['notification_channel']);
            }
        }

        return $dataSource;
    }
}
