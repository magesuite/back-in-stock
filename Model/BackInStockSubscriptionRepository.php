<?php

namespace MageSuite\BackInStock\Model;

class BackInStockSubscriptionRepository implements \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
{
    /**
     * @var ResourceModel\BackInStockSubscription
     */
    protected $backInStockSubscriptionResource;

    /**
     * @var BackInStockSubscriptionFactory
     */
    protected $backInStockSubscriptionFactory;

    /**
     * @var ResourceModel\BackInStockSubscription\CollectionFactory
     */
    protected $subscriptionCollectionFactory;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription $backInStockSubscriptionResource,
        \MageSuite\BackInStock\Model\BackInStockSubscriptionFactory $backInStockSubscriptionFactory,
        \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory
    ) {
        $this->backInStockSubscriptionResource = $backInStockSubscriptionResource;
        $this->backInStockSubscriptionFactory = $backInStockSubscriptionFactory;
        $this->subscriptionCollectionFactory = $subscriptionCollectionFactory;
    }

    public function getById($id)
    {
        $subscription = $this->backInStockSubscriptionFactory->create();
        $subscription->load($id);
        if (!$subscription->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Back in stock subscription with id "%1" does not exist.', $id));
        }

        return $subscription;
    }

    /**
     * @inheirtDoc
     */
    public function get(int $productId, string $identifyByField, $identifyByValue, int $storeId): \MageSuite\BackInStock\Model\BackInStockSubscription //phpcs:ignore
    {
        $collection = $this->subscriptionCollectionFactory->create();

        $collection->addFieldToFilter('product_id', ['eq' => $productId]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter($identifyByField, ['eq' => $identifyByValue]);
        $collection->addFieldToFilter('is_removed', ['eq' => 0]);

        return $collection->getFirstItem();
    }

    public function save(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription)
    {
        try {
            $this->backInStockSubscriptionResource->save($backInStockSubscription);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save this entity: %1',
                $exception->getMessage()
            ));
        }
        return $backInStockSubscription;
    }

    public function delete(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription)
    {
        try {
            $this->backInStockSubscriptionResource->delete($backInStockSubscription);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__(
                'Could not delete this entity: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    public function unsubscribe(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription, bool $isHistoricalDataKept = false)
    {
        if (!$isHistoricalDataKept) {
            $this->delete($backInStockSubscription);
            return;
        }

        $backInStockSubscription->setIsRemoved(true);
        $this->save($backInStockSubscription);
    }

    public function subscriptionExist(int $productId, string $identifyByField, $identifyByValue, int $storeId) //phpcs:ignore
    {
        $collection = $this->subscriptionCollectionFactory->create();

        $collection->addFieldToFilter('product_id', ['eq' => $productId]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter($identifyByField, ['eq' => $identifyByValue]);
        $collection->addFieldToFilter('is_removed', ['eq' => 0]);

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function generateToken($email, $customerId)
    {
        return substr(md5(json_encode(['customer_email' => $email, 'customer_id' => $customerId, 'token' => md5(random_bytes(20))])), 0, 8); //phpcs:ignore
    }
}
