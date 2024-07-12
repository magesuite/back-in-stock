<?php

namespace MageSuite\BackInStock\Service;

class SubscriptionEntityCreator
{
    /**
     * @var array
     */
    protected $creatorsByChannel;

    public function __construct($creatorsByChannel = [])
    {
        $this->creatorsByChannel = $creatorsByChannel;
    }

    public function subscribe($params)
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
