<?php

namespace MageSuite\BackInStock\Helper;

class Subscription
{
    /**
     * @param bool $isConfirmed
     * @param bool $isUnsubscribed
     * @param string $subscriptionAddDate
     * @return bool
     */
    public function isSubscriptionRejected(bool $isConfirmed, bool $isUnsubscribed, string $subscriptionAddDate): bool
    {
        if (!$isConfirmed && !$isUnsubscribed && $this->isConfirmationDeadlinePassed($subscriptionAddDate)) {
            return true;
        }

        return false;
    }

    /**
     * @param string $subscriptionAddDate
     * @return bool
     */
    public function isConfirmationDeadlinePassed(string $subscriptionAddDate): bool
    {
        return $this->getSubscriptionConfirmationDeadline($subscriptionAddDate) < new \DateTime();
    }

    /**
     * @param string $subscriptionAddDate
     * @return \DateTime
     * @throws \Exception
     */
    public function getSubscriptionConfirmationDeadline(string $subscriptionAddDate): \DateTime
    {
        $subscriptionAddDateTime = new \DateTime($subscriptionAddDate);
        return $subscriptionAddDateTime->add(
            new \DateInterval(
                sprintf('PT%dH', \MageSuite\BackInStock\Model\BackInStockSubscription::SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS)
            )
        );
    }
}
