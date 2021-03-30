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
    )
    {

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

    public function save(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription)
    {
        try {
            $this->backInStockSubscriptionResource->save($subscription);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save this entity: %1',
                $exception->getMessage()
            ));
        }
        return $subscription;
    }

    public function delete(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscription)
    {
        try {
            $this->backInStockSubscriptionResource->delete($subscription);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__(
                'Could not delete this entity: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    public function subscriptionExist($productId, $identifyByField, $identifyByValue, $storeId)
    {
        $collection = $this->subscriptionCollectionFactory->create();

        $collection->addFieldToFilter('product_id', ['eq' => $productId]);
        $collection->addFieldToFilter('store_id', ['eq' => $storeId]);
        $collection->addFieldToFilter($identifyByField, ['eq' => $identifyByValue]);

        if($collection->getSize()){
            return true;
        }

        return false;
    }

    public function generateToken($email, $customerId)
    {
        return substr(md5(json_encode(['customer_email' => $email, 'customer_id' => $customerId, 'token' => md5(random_bytes(20))])), 0, 8);
    }
}
