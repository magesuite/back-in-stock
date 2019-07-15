<?php
namespace MageSuite\BackInStock\Service;

class NotificationQueueSender
{
    const MANUAL_NOTIFICATION = 'manual_notification';
    const AUTOMATIC_NOTIFICATION = 'automatic_notification';

    protected $notificationTypeMap = [
        self::MANUAL_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::MANUAL_NOTIFICATION_TEMPLATE,
        self::AUTOMATIC_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::AUTOMATIC_NOTIFICATION_TEMPLATE,
        ];

    /**
     * @var \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory
     */
    protected $notificationCollectionFactory;

    /**
     * @var EmailSender
     */
    protected $emailSender;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;
    /**
     * @var \MageSuite\BackInStock\Api\NotificationRepositoryInterface
     */
    protected $notificationRepository;


    public function __construct(
        \MageSuite\BackInStock\Model\ResourceModel\Notification\CollectionFactory $notificationCollectionFactory,
        \MageSuite\BackInStock\Service\EmailSender $emailSender,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Api\NotificationRepositoryInterface $notificationRepository
    )
    {
        $this->notificationCollectionFactory = $notificationCollectionFactory;
        $this->emailSender = $emailSender;
        $this->productRepository = $productRepository;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->notificationRepository = $notificationRepository;
    }

    public function send()
    {
        $notificationCollection = $this->notificationCollectionFactory->create();

        /** @var \MageSuite\BackInStock\Model\Notification $notification */
        foreach ($notificationCollection as $notification) {
            $this->emailSender->sendMail($notification->getCustomerEmail(), $this->getTemplateParams($notification), $this->getEmailTemplateId($notification->getNotificationType()), $notification->getStoreId(), $notification->getCustomerId());

            $subscription = $this->backInStockSubscriptionRepository->getById($notification->getSubscriptionId());

            $subscription
                ->setSendCount($subscription->getSendCount() + 1)
                ->setSendDate(date("Y-m-d H:i:s"))
                ->setWasNotificationSent(1);

            $this->backInStockSubscriptionRepository->save($subscription);

            $this->notificationRepository->delete($notification);
        }

        return $this;
    }

    public function getEmailTemplateId($notificationType)
    {
        $notificationTypeMap = $this->notificationTypeMap;

        return $notificationTypeMap[$notificationType];
    }

    public function getTemplateParams($notification)
    {

        if($notification->getNotificationType() == self::MANUAL_NOTIFICATION){
            return $this->getManualNotificationParams($notification);
        }

        if($notification->getNotificationType() == self::AUTOMATIC_NOTIFICATION){
            return $this->getAutomaticNotificationParams($notification);
        }

        return [];
    }

    /**
     * @param $notification \MageSuite\BackInStock\Model\Notification
     * @return array
     */
    public function getManualNotificationParams($notification)
    {
        return [
            'notification_message' => $notification->getMessage()
        ];
    }

    /**
     * @param $notification \MageSuite\BackInStock\Model\Notification
     * @return array
     */
    public function getAutomaticNotificationParams($notification)
    {
        $product = $this->productRepository->getById($notification->getProductId(), false, $notification->getStoreId());

        return [
            'product_name' => $product->getName(),
            'product_sku' => $product->getSku(),
            'product_url' => $product->getProductUrl()
        ];
    }
}