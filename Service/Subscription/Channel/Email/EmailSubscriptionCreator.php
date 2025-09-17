<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service\Subscription\Channel\Email;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSubscriptionCreator
{
    protected array $templateParams = [];

    public function __construct(
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        protected \Magento\Customer\Model\SessionFactory $customerSession,
        protected \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        protected \MageSuite\BackInStock\Service\EmailSender $emailSender,
        protected \MageSuite\BackInStock\Service\Subscription\ProductResolver $productResolver,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        protected \Laminas\Validator\EmailAddress $emailAddressValidator
    ) {
    }

    public function subscribe(array $params): void
    {
        $storeId = (int)$this->storeManager->getStore()->getId();
        $customerSession = $this->customerSession->create();
        $customerId = (int)$customerSession->getCustomerId();
        $email = $params['email'];
        $product = $this->productResolver->resolve($params);
        $productId = (int)$product->getId();

        if (!$this->validateEmail($email)) {
            throw new \Exception('Invalid email address.'); //phpcs:ignore
        }

        $guestSubscriptionExists = $this->subscriptionExist($productId, 0, $email, $storeId);
        $customerSubscriptionExists = $customerId && $this->subscriptionExist($productId, $customerId, $email, $storeId);

        if (!$guestSubscriptionExists && !$customerSubscriptionExists) {
            $subscription = $this->createNewSubscription($product, $customerId, $email, $storeId);
            $this->sendConfirmationEmail($subscription, $params, $storeId, $customerId);
            return;
        }

        //update subscription when customer subscribes second time as logged-in user
        if ($customerId && $guestSubscriptionExists) {
            $subscription = $this->updateSubscriptionCustomerId($productId, $customerId, $email, $storeId);
        } else {
            $subscription = $this->getExistingSubscription($productId, $customerId, $email, $storeId);
        }

        if (!$this->canSubscriptionBeReset($subscription)) {
            return;
        }

        $subscription = $this->resetExistingSubscription($subscription, $customerId, $email);
        $this->sendConfirmationEmail($subscription, $params, $storeId, $customerId);
    }

    public function sendConfirmationRequest( //phpcs:ignore
        string $email,
        array $params,
        string $templateConfigPath,
        int $storeId,
        int $customerId
    ): void {
        $this->emailSender->sendMail($email, $params, $templateConfigPath, $storeId, $customerId);
    }

    public function setTemplateParams(
        \MageSuite\BackInStock\Model\BackInStockSubscription $subscription
    ): void
    {
        $this->templateParams = [
            'email' => $subscription->getCustomerEmail(),
            'customer_id' => $subscription->getCustomerId(),
            'confirm_url' => $this->getConfirmUrl($subscription),
            'unsubscribe_url' => $this->getUnsubscribeUrl($subscription)
        ];
    }

    public function getConfirmUrl(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): string
    {
        return $this->subscriptionHelper->getConfirmUrl($subscription);
    }

    public function getUnsubscribeUrl(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): string
    {
        return $this->subscriptionHelper->getUnsubscribeUrl($subscription);
    }

    public function validateEmail(string $email): bool
    {
        return $this->emailAddressValidator->isValid(trim($email));
    }

    public function subscriptionExist( //phpcs:ignore
        int $productId,
        int $customerId,
        string $email,
        int $storeId
    ): bool {
        $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_EMAIL;
        $identifyByValue = $email;

        if ($customerId) {
            $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_ID;
            $identifyByValue = (string) $customerId;
        }

        return $this->backInStockSubscriptionRepository->subscriptionExist(
            $productId,
            $identifyByField,
            $identifyByValue,
            $storeId
        );
    }

    public function getExistingSubscription( //phpcs:ignore
        int $productId,
        int $customerId,
        string $email,
        int $storeId
    ): \MageSuite\BackInStock\Model\BackInStockSubscription {
        $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_EMAIL;
        $identifyByValue = $email;

        if ($customerId) {
            $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_ID;
            $identifyByValue = $customerId;
        }

        return $this->backInStockSubscriptionRepository->get(
            $productId,
            $identifyByField,
            $identifyByValue,
            $storeId
        );
    }

    public function canSubscriptionBeReset(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription): bool
    {
        if ($subscription->isCustomerConfirmed() && !$subscription->isCustomerUnsubscribed()) {
            return false;
        }
        if (
            !$subscription->isCustomerConfirmed() &&
            !$subscription->isCustomerUnsubscribed() &&
            !$subscription->isConfirmationDeadlinePassed()
        ) {
            return false;
        }

        return true;
    }

    public function createNewSubscription( //phpcs:ignore
        \Magento\Catalog\Api\Data\ProductInterface $product,
        int $customerId,
        string $email,
        int $storeId
    ): \MageSuite\BackInStock\Model\BackInStockSubscription {
        $token = $this->backInStockSubscriptionRepository->generateToken($email, $customerId);
        $isConfirmationRequired = $this->configuration->isConfirmationRequired();

        $subscription = $this->backInStockSubscription
            ->setCustomerId($customerId)
            ->setCustomerEmail($email)
            ->setCustomerConfirmed(!$isConfirmationRequired)
            ->setProductId($product->getId())
            ->setParentProductId($product->getParentProductId())
            ->setStoreId($storeId)
            ->setNotificationChannel('email')
            ->setToken($token);
        $subscription = $this->backInStockSubscriptionRepository->save($subscription);

        return $subscription;
    }

    public function updateSubscriptionCustomerId( //phpcs:ignore
        int $productId,
        int $customerId,
        string $email,
        int $storeId
    ): \MageSuite\BackInStock\Model\BackInStockSubscription {
        $subscription = $this->getExistingSubscription($productId, 0, $email, $storeId);
        $subscription->setCustomerId($customerId);

        return $this->backInStockSubscriptionRepository->save($subscription);
    }

    public function resetExistingSubscription(
        \MageSuite\BackInStock\Model\BackInStockSubscription $subscription,
        int $customerId,
        string $email
    ): \MageSuite\BackInStock\Model\BackInStockSubscription {
        $token = $this->backInStockSubscriptionRepository->generateToken($email, $customerId);
        $isConfirmationRequired = $this->configuration->isConfirmationRequired();

        $subscription = $this->backInStockSubscriptionRepository->getById((int) $subscription->getId());
        $subscription
            ->setCustomerConfirmed(!$isConfirmationRequired)
            ->setCustomerUnsubscribed(false)
            ->setAddDate(new \DateTime())
            ->setToken($token);

        $subscription = $this->backInStockSubscriptionRepository->save($subscription);

        return $subscription;
    }

    protected function sendConfirmationEmail( //phpcs:ignore
        \MageSuite\BackInStock\Model\BackInStockSubscription $subscription,
        array $params,
        int $storeId,
        int $customerId
    ): void {
        if (!$this->configuration->isConfirmationRequired()) {
            return;
        }

        $this->setTemplateParams($subscription);

        $this->sendConfirmationRequest(
            $params['email'],
            $this->templateParams,
            \MageSuite\BackInStock\Helper\Configuration::CONFIRMATION_EMAIL_CONFIG_PATH,
            $storeId,
            $customerId
        );
    }
}
