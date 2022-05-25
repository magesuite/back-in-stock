<?php

namespace MageSuite\BackInStock\Service;

class Unsubscriber
{
    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    public function __construct(
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->configuration = $configuration;
    }

    public function execute(array $subscriptionIds)
    {
        $isHistoricalDataKept = $this->configuration->isHistoricalDataKept();

        foreach ($subscriptionIds as $subscriptionId) {
            try {
                $subscription = $this->backInStockSubscriptionRepository->getById((int)$subscriptionId);
                $this->backInStockSubscriptionRepository->unsubscribe($subscription, $isHistoricalDataKept);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $exception) { //phpcs:ignore
            }
        }
    }
}
