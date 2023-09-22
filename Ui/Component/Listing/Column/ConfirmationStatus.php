<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class ConfirmationStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PENDING = 'pending';

    /**
     * @var \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus
     */
    protected $confirmationStatusSource;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus $confirmationStatusSource,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->confirmationStatusSource = $confirmationStatusSource;
    }

    /**
     * @ingeritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $columnContent = sprintf('<span class="%s">%s</span>', $this->getStatusClass($item['confirmation_status']), $this->getReadableStatus($item['confirmation_status']));
                $item['confirmation_status'] = $columnContent;
            }
        }

        return $dataSource;
    }

    /**
     * @param string $status
     * @return string
     */
    public function getReadableStatus(string $status): string
    {
        $statuses = $this->confirmationStatusSource->getStatuses();

        return $statuses[$status] ?? $statuses[\MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus::STATUS_PENDING];
    }

    /**
     * @param string $status
     * @return string
     */
    public function getStatusClass(string $status): string
    {
        $statusClasses = [
            \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus::STATUS_CONFIRMED => 'grid-severity-notice',
            \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus::STATUS_REJECTED => 'grid-severity-critical',
            \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus::STATUS_UNSUBSCRIBED => 'grid-severity-critical',
            \MageSuite\BackInStock\Model\Source\Subscriptions\ConfirmationStatus::STATUS_PENDING => 'grid-severity-minor'
        ];

        return $statusClasses[$status] ?? 'grid-severity-minor';
    }
}
