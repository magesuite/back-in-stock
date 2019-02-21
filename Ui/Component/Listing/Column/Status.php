<?php
namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class Status extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    )
    {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $item['was_notification_sent'] ? __('Sent') : __('Not sent');
                $item['status'] = $status;
            }
        }

        return $dataSource;
    }
}