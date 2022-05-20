<?php

namespace MageSuite\BackInStock\Helper;

class Subscription
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(\Magento\Framework\UrlInterface $url)
    {
        $this->url = $url;
    }

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

    public function getConfirmUrl($subscription)
    {
        return $this->url->setScope($subscription->getStoreId())
            ->getUrl('backinstock/notification/confirm', ['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
    }

    public function getUnsubscribeUrl($subscription)
    {
        return $this->url->setScope($subscription->getStoreId())
            ->getUrl('backinstock/notification/unsubscribe', ['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
    }
}
