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
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
     */
    public function save(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription);

    /**
     * @param \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
     * @return void
     */
    public function delete(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription);

    /**
     * @param string|int $productId
     * @param string|int $customerId
     * @param string $email
     * @param string $storeId
     * @return mixed
     */
    public function subscriptionExist($productId, $customerId, $email, $storeId);

    /**
     * @param $email
     * @param $customerId
     * @return mixed
     */
    public function generateToken($email, $customerId);
}