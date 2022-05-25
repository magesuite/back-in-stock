<?php

namespace MageSuite\BackInStock\Api;

interface BackInStockSubscriptionRepositoryInterface
{
    /**
     * @param int $id
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * @param int $productId
     * @param string $identifyByField
     * @param mixed $identifyByValue
     * @param int $storeId
     * @return mixed
     */
    public function get(int $productId, string $identifyByField, $identifyByValue, int $storeId): \MageSuite\BackInStock\Model\BackInStockSubscription; //phpcs:ignore

    /**
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     */
    public function save(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription);

    /**
     * @param Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return mixed
     */
    public function delete(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription);

    /**
     * @param Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @param bool $isHistoricalDataKept
     * @return mixed
     */
    public function unsubscribe(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription, bool $isHistoricalDataKept = false);

    /**
     * @param int $productId
     * @param string $identifyByField
     * @param mixed $identifyByValue
     * @param int $storeId
     * @return mixed
     */
    public function subscriptionExist(int $productId, string $identifyByField, $identifyByValue, int $storeId); //phpcs:ignore

    /**
     * @param $email
     * @param $customerId
     * @return mixed
     */
    public function generateToken($email, $customerId);
}
