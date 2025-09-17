<?php

namespace MageSuite\BackInStock\Api;

interface BackInStockSubscriptionRepositoryInterface
{
    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface;

    /**
     * @param int $productId
     * @param string $identifyByField
     * @param string $identifyByValue
     * @param int $storeId
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     */
    public function get( //phpcs:ignore
        int $productId, 
        string $identifyByField,
        string $identifyByValue, 
        int $storeId
    ): \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface;

    /**
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     */
    public function save(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
    ): \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface;

    /**
     * @param Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return bool
     */
    public function delete(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
    ): bool;

    /**
     * @param Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @param bool $isHistoricalDataKept
     * @return ?\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     */
    public function unsubscribe(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription, 
        bool $isHistoricalDataKept = false
    ): ?\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface;

    /**
     * @param int $productId
     * @param string $identifyByField
     * @param string $identifyByValue
     * @param int $storeId
     * @return bool
     */
    public function subscriptionExist( //phpcs:ignore
        int $productId,
        string $identifyByField,
        string $identifyByValue,
        int $storeId
    ): bool;

    /**
     * @param string $email
     * @param int $customerId
     * @return string
     */
    public function generateToken(string $email, int $customerId): string;
}
