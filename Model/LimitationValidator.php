<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class LimitationValidator
{
    public function __construct(
        protected \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        protected \MageSuite\BackInStock\Helper\Configuration $config
    ) {
    }

    public function isMinTimeValid(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): bool
    {
        $lastSend = (string)$subscription->getSendDate();
        $minTime = $this->config->getMinTimeBetweenNotifications(); // in seconds

        if (!$lastSend || !$minTime) {
            return true;
        }

        $currentTime = $this->dateTime->gmtTimestamp();

        $nextAllowed  = strtotime(sprintf('%s + %s seconds', $lastSend , $minTime));

        return $currentTime >= $nextAllowed;
    }


    public function isDailyLimitExceeded(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription): bool
    {
        $lastSend = (string)$subscription->getSendDate();
        $dailyLimit = $this->config->getDailyNotificationLimit();
        $dailySendCount = (int)$subscription->getSendCountDaily();

        if (!$lastSend || !$dailyLimit || $dailySendCount <= 0) {
            return false;
        }

        $lastSendDate = date('Y-m-d', strtotime($lastSend));
        $currentDate = date('Y-m-d', $this->dateTime->gmtTimestamp());

        if ($lastSendDate !== $currentDate) {
            $subscription->setSendCountDaily(0);
            return false;
        }

        return $dailySendCount >= $dailyLimit;
    }
}
