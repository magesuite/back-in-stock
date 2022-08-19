<?php

namespace MageSuite\BackInStock\Model;

class NotificationRepository implements \MageSuite\BackInStock\Api\NotificationRepositoryInterface
{

    /**
     * @var ResourceModel\Notification
     */
    protected $notificationResource;
    /**
     * @var NotificationFactory
     */
    protected $notificationFactory;
    /**
     * @var ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollectionFactory;

    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\Notification $notificationResource,
        \MageSuite\BackInStock\Model\NotificationFactory $notificationFactory,
        \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory
    ) {

        $this->notificationResource = $notificationResource;
        $this->notificationFactory = $notificationFactory;
        $this->notificationCollectionFactory = $notificationCollectionFactory;
    }

    public function getById($id)
    {
        $notification = $this->notificationFactory->create();
        $notification->load($id);
        if (!$notification->getId()) {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Notification with id "%1" does not exist.', $id));
        }
        return $notification;
    }

    public function save(\MageSuite\BackInStock\Api\Data\NotificationInterface $notification)
    {
        try {
            $this->notificationResource->save($notification);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(__(
                'Could not save this entity: %1',
                $exception->getMessage()
            ));
        }
        return $notification;
    }

    public function delete(\MageSuite\BackInStock\Api\Data\NotificationInterface $notification)
    {
        try {
            $this->notificationResource->delete($notification);
        } catch (\Exception $exception) {
            throw new \Magento\Framework\Exception\CouldNotDeleteException(__(
                'Could not delete this entity: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }
}
