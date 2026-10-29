<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service\Subscription\Channel\Email;

class ActivationEmailSender
{
    public function __construct(
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \MageSuite\BackInStock\Api\NotificationProductDataResolverInterface $notificationProductDataResolver,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        protected \MageSuite\BackInStock\Service\EmailSender $emailSender,
        protected \Psr\Log\LoggerInterface $logger
    ) {
    }

    public function send(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): void
    {
        $storeId = (int)$subscription->getStoreId();

        if (!$this->configuration->isActivationEmailEnabled($storeId)) {
            return;
        }

        $productData = $this->notificationProductDataResolver->getProductData($subscription);

        if ($productData->isEmpty()) {
            $this->logger->error(sprintf('Back in stock activation email for subscription %d was not sent, because its product was not found.', $subscription->getId()));

            return;
        }

        $templateVariables = $this->getTemplateVariables($subscription, $productData);

        $this->emailSender->sendMail($subscription->getCustomerEmail(), $templateVariables, \MageSuite\BackInStock\Helper\Configuration::ACTIVATION_EMAIL_CONFIG_PATH, $storeId, (int)$subscription->getCustomerId());
    }

    protected function getTemplateVariables(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription, \Magento\Framework\DataObject $productData): array
    {
        return [
            'product_name' => $productData->getData('name'),
            'product_url' => $productData->getData('product_url'),
            'product_image_url' => $productData->getData('product_image_url'),
            'unsubscribe_url' => $this->subscriptionHelper->getUnsubscribeUrl($subscription),
        ];
    }
}
