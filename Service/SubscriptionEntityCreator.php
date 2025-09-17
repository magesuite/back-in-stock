<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service;

class SubscriptionEntityCreator
{
    public function __construct(
        protected array $creatorsByChannel = []
    ) {
    }

    public function subscribe(array $params): void
    {
        $channel = $params['notification_channel'] ?? null;

        if (!$channel) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Notification channel has not been specified.'));
        }

        if (!isset($this->creatorsByChannel[$channel])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Creator for specified channel does not exist'));
        }

        $this->creatorsByChannel[$channel]->subscribe($params);
    }
}
