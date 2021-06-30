<?php

namespace MageSuite\BackInStock\Ui\Component\Listing\Column;

class ConfirmationStatus extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PENDING = 'pending';

    /**
     * @var \MageSuite\BackInStock\Helper\Subscription
     */
    protected $subscriptionHelper;

    /**
     * @param \Magento\Framework\View\Element\UiComponent\ContextInterface $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper
     * @param array $components
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context,
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory,
        \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        array $components = [],
        array $data = []
    )
    {
        $this->subscriptionHelper = $subscriptionHelper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @ingeritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $status = $this->getConfirmationStatus($item);
                $columnContent = sprintf('<span class="%s">%s</span>', $this->getStatusClass($status), $this->getReadableStatus($status));
                $item['confirmation_status'] = $columnContent;
            }
        }

        return $dataSource;
    }

    /**
     * @param array $subscriptionItem
     * @return string
     */
    public function getConfirmationStatus(array $subscriptionItem): string
    {
        if ($subscriptionItem['customer_confirmed'] && !$subscriptionItem['customer_unsubscribed']) {
            return self::STATUS_CONFIRMED;
        }
        if ($subscriptionItem['customer_unsubscribed']) {
            return self::STATUS_UNSUBSCRIBED;
        }
        if ($this->subscriptionHelper->isSubscriptionRejected($subscriptionItem['customer_confirmed'], $subscriptionItem['customer_unsubscribed'], $subscriptionItem['add_date'])) {
            return self::STATUS_REJECTED;
        }

        return self::STATUS_PENDING;
    }

    /**
     * @param string $status
     * @return string
     */
    public function getReadableStatus(string $status): string
    {
        $statuses = [
            self::STATUS_CONFIRMED => __('Confirmed'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_UNSUBSCRIBED => __('Unsubscribed'),
            self::STATUS_PENDING => __('Pending')
        ];

        return $statuses[$status];
    }

    /**
     * @param string $status
     * @return string
     */
    public function getStatusClass(string $status): string
    {
        $statusClasses = [
            self::STATUS_CONFIRMED => 'grid-severity-notice',
            self::STATUS_REJECTED => 'grid-severity-critical',
            self::STATUS_UNSUBSCRIBED => 'grid-severity-critical',
            self::STATUS_PENDING => 'grid-severity-minor'
        ];

        return $statusClasses[$status];
    }
}
