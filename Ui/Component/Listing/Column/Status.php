<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item['status'] = $this->formatSendNotificationStatus($item['status']);
            }
        }

        return $dataSource;
    }

    private function formatSendNotificationStatus($status)
    {
        $status = str_replace('_', ' ', $status);
        return ucfirst($status);
    }
}
