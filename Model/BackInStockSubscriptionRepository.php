<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Model;

class BackInStockSubscriptionRepository implements \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
{
    public function __construct(
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription $backInStockSubscriptionResource,
        protected \MageSuite\BackInStock\Model\BackInStockSubscriptionFactory $backInStockSubscriptionFactory,
        protected \MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\CollectionFactory $subscriptionCollectionFactory
    ) {
    }

    public function getById(int $id): \MageSuite\BackInStock\Model\BackInStockSubscription
    {
        $subscription = $this->backInStockSubscriptionFactory->create();
        $subscription->load($id);

        if (!$subscription->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Back in stock subscription with id "%1" does not exist.', $id));
        }

        return $subscription;
    }

    public function get( //phpcs:ignore
        int $productId,
        string $identifyByField,
        string $identifyByValue,
        int $storeId
    ): \MageSuite\BackInStock\Model\BackInStockSubscription {
        $collection = $this->subscriptionCollectionFactory->create();

        $collection->addFieldToFilter('product_id', ['eq' => $productId]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter($identifyByField, ['eq' => $identifyByValue]);
        $collection->addFieldToFilter('is_removed', ['eq' => 0]);

        return $collection->getFirstItem();
    }

    public function save(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
    ): \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface {
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

    public function delete(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription
    ): bool {
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

    public function unsubscribe(
        \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $backInStockSubscription,
        bool $isHistoricalDataKept = false
    ): ?\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface {
        if (!$isHistoricalDataKept) {
            $this->delete($backInStockSubscription);
            return null;
        }

        $backInStockSubscription->setIsRemoved(true);

        return $this->save($backInStockSubscription);
    }

    public function subscriptionExist( //phpcs:ignore
        int $productId,
        string $identifyByField,
        string $identifyByValue,
        int $storeId
    ): bool {
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

    public function generateToken(string $email, int $customerId): string
    {
        $data = [
            'customer_email' => $email,
            'customer_id' => $customerId,
            'token' => hash('sha256', random_bytes(20))
        ];

        return substr(hash('sha256', json_encode($data)), 0, 8);
    }
}
