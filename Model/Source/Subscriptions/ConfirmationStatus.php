<?php

namespace MageSuite\BackInStock\Model\Source\Subscriptions;

class ConfirmationStatus implements \Magento\Framework\Data\OptionSourceInterface
{
    public const STATUS_CONFIRMED = 'confirmed';

    public const STATUS_UNSUBSCRIBED = 'unsubscribed';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_PENDING = 'pending';

    public function getStatuses()
    {
        return [
            self::STATUS_CONFIRMED => __('Confirmed'),
            self::STATUS_REJECTED => __('Rejected'),
            self::STATUS_UNSUBSCRIBED => __('Unsubscribed'),
            self::STATUS_PENDING => __('Pending')
        ];
    }

    public function toOptionArray()
    {
        $options = [];
        foreach ($this->getStatuses() as $value => $label) {
            $options[] = ['value' => $value, 'label' => $label];
        }
        return $options;
    }
}
