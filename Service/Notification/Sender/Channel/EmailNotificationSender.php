<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service\Notification\Sender\Channel;

class EmailNotificationSender
{
    protected array $notificationTypeMap = [
        \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::MANUAL_NOTIFICATION_TEMPLATE,
        \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION => \MageSuite\BackInStock\Api\Data\NotificationInterface::AUTOMATIC_NOTIFICATION_TEMPLATE,
    ];

    public function __construct(
        protected \MageSuite\BackInStock\Service\EmailSender $emailSender,
        protected \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper
    ) {
    }

    public function send(\MageSuite\BackInStock\Model\Notification $notification, \MageSuite\BackInStock\Model\BackInStockSubscription $subscription): string
    {
        $templateParams = $this->getTemplateParams($notification, $subscription);
        $templateConfigPath = $this->getEmailTemplateId((string)$notification->getNotificationType());

        return $this->emailSender->sendMail($subscription->getCustomerEmail(), $templateParams, $templateConfigPath, $subscription->getStoreId(), $subscription->getCustomerId());
    }

    public function getEmailTemplateId(string $notificationType): string
    {
        return $this->notificationTypeMap[$notificationType];
    }

    public function getTemplateParams(\MageSuite\BackInStock\Model\Notification $notification, \MageSuite\BackInStock\Model\BackInStockSubscription $subscription): array
    {
        if ($notification->getNotificationType() == \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION) {
            return $this->getManualNotificationParams($notification);
        }

        if ($notification->getNotificationType() == \MageSuite\BackInStock\Service\NotificationQueueSender::AUTOMATIC_NOTIFICATION) {
            return $this->getAutomaticNotificationParams($notification, $subscription);
        }

        return [];
    }

    public function getManualNotificationParams(\MageSuite\BackInStock\Model\Notification $notification): array
    {
        return [
            'notification_message' => $notification->getMessage()
        ];
    }

    public function getAutomaticNotificationParams(\MageSuite\BackInStock\Model\Notification $notification, \MageSuite\BackInStock\Model\BackInStockSubscription $subscription): array
    {
        $productData = $this->notificationProductDataResolver->getProductData($subscription);

        $params = [
            'product_name' => $productData->getName(),
            'product_sku' => $productData->getSku(),
            'product_url' => $productData->getProductUrl()
        ];

        if (!$this->configuration->isSubscriptionRemovedAfterNotification()) {
            $params['unsubscribe_url'] = $this->subscriptionHelper->getUnsubscribeUrl($subscription);
        }

        return $params;
    }
}
