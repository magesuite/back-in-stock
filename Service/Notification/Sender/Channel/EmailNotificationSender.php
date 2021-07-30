<?php

namespace MageSuite\BackInStock\Service\Notification\Sender\Channel;

class EmailNotificationSender
{
    protected $notificationTypeMap = [
        \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::MANUAL_NOTIFICATION_TEMPLATE,
        \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::AUTOMATIC_NOTIFICATION_TEMPLATE,
    ];

    /**
     * @var \MageSuite\BackInStock\Service\EmailSender
     */
    protected $emailSender;

    /**
     * @var \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface
     */
    protected $notificationProductDataResolver;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \MageSuite\BackInStock\Service\EmailSender $emailSender,
        \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver
    ) {
        $this->emailSender = $emailSender;
        $this->notificationProductDataResolver = $notificationProductDataResolver;
    }

    public function send($notification, $subscription)
    {
        return  $this->emailSender->sendMail(
            $subscription->getCustomerEmail(),
            $this->getTemplateParams($notification, $subscription),
            $this->getEmailTemplateId($notification->getNotificationType()),
            $subscription->getStoreId(),
            $subscription->getCustomerId()
        );
    }

    public function getEmailTemplateId($notificationType)
    {
        $notificationTypeMap = $this->notificationTypeMap;

        return $notificationTypeMap[$notificationType];
    }

    public function getTemplateParams($notification, $subscription)
    {
        if ($notification->getNotificationType() == \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION) {
            return $this->getManualNotificationParams($notification, $subscription);
        }

        if ($notification->getNotificationType() == \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION) {
            return $this->getAutomaticNotificationParams($notification, $subscription);
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
     * @param $subscription \MageSuite\BackInStock\Model\BackInStockSubscription
     * @return array
     */
    public function getAutomaticNotificationParams($notification, $subscription)
    {
        $productData = $this->notificationProductDataResolver->getProductData($subscription);

        return [
            'product_name' => $productData->getName(),
            'product_sku' => $productData->getSku(),
            'product_url' => $productData->getProductUrl()
        ];
    }
}
