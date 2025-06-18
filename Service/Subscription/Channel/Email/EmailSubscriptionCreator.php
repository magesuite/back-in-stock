<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service\Subscription\Channel\Email;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EmailSubscriptionCreator
{
    protected $customer = null; //phpcs:ignore
    protected array $templateParams = [];

    public function __construct( //phpcs:ignore
        protected \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        protected \Magento\Customer\Model\SessionFactory $customerSession,
        protected \Magento\Framework\Message\ManagerInterface $messageManager,
        protected \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        protected \MageSuite\BackInStock\Service\EmailSender $emailSender,
        protected \MageSuite\BackInStock\Service\Subscription\ProductResolver $productResolver,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \MageSuite\BackInStock\Model\BackInStockSubscriptionFactory $backInStockSubscriptionFactory,
        protected \MageSuite\BackInStock\Helper\Subscription $subscriptionHelper,
        protected \Laminas\Validator\EmailAddress $emailAddressValidator
    ) {
    }

    public function subscribe($params) //phpcs:ignore
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
            $this->sendConfirmationEmail($subscription, $product, $params, $storeId, $customerId);
            return;
        }

        //update subscription when customer subscribes second time as logged-in user
        if ($customerId && $guestSubscriptionExists) {
            $subscription = $this->updateSubscriptionCustomerId($productId, $customerId, $email, $storeId);
            $subscription = $this->resetExistingSubscription($subscription, $customerId, $email);
            $this->sendConfirmationEmail($subscription, $product, $params, $storeId, $customerId);
            return;
        }

        $subscription = $this->getExistingSubscription($productId, $customerId, $email, $storeId);

        if ($this->canSubscriptionBeReset($subscription)) {
            $subscription = $this->resetExistingSubscription($subscription, $customerId, $email);
        }

        $this->sendConfirmationEmail($subscription, $product, $params, $storeId, $customerId);
    }

    public function getCustomer() //phpcs:ignore
    {
        if (!$this->customer) {
            $this->customer = $this->customerSession->getCustomer();
        }

        return $this->customer;
    }

    public function sendConfirmationRequest($email, $params, $templateConfigPath, $storeId, $customerId) //phpcs:ignore
    {
        $this->emailSender->sendMail($email, $params, $templateConfigPath, $storeId, $customerId);
    }

    public function setTemplateParams($subscription, $product) //phpcs:ignore
    {
        $this->templateParams = [
            'email' => $subscription->getCustomerEmail(),
            'product' => $product,
            'customer_id' => $subscription->getCustomerId(),
            'confirm_url' => $this->getConfirmUrl($subscription),
            'unsubscribe_url' => $this->getUnsubscribeUrl($subscription)
        ];
    }

    public function getConfirmUrl($subscription) //phpcs:ignore
    {
        return $this->subscriptionHelper->getConfirmUrl($subscription);
    }

    public function getUnsubscribeUrl($subscription) //phpcs:ignore
    {
        return $this->subscriptionHelper->getUnsubscribeUrl($subscription);
    }

    public function validateEmail($email) //phpcs:ignore
    {
        return $this->emailAddressValidator->isValid(trim($email));
    }

    public function subscriptionExist($productId, $customerId, $email, $storeId) //phpcs:ignore
    {
        $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_EMAIL;
        $identifyByValue = $email;

        if ($customerId) {
            $identifyByField = \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::CUSTOMER_ID;
            $identifyByValue = $customerId;
        }

        return $this->backInStockSubscriptionRepository->subscriptionExist(
            $productId,
            $identifyByField,
            $identifyByValue,
            $storeId
        );
    }

    public function getExistingSubscription(int $productId, int $customerId, string $email, int $storeId): \MageSuite\BackInStock\Model\BackInStockSubscription //phpcs:ignore
    {
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
        if (!$subscription->isCustomerConfirmed() && !$subscription->isCustomerUnsubscribed() && !$subscription->isConfirmationDeadlinePassed()) {
            return false;
        }

        return true;
    }

    public function createNewSubscription(\Magento\Catalog\Api\Data\ProductInterface $product, int $customerId, string $email, int $storeId): \MageSuite\BackInStock\Model\BackInStockSubscription //phpcs:ignore
    {
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

        $subscription = $this->backInStockSubscriptionRepository->getById($subscription->getId());
        $subscription
            ->setCustomerConfirmed(!$isConfirmationRequired)
            ->setCustomerUnsubscribed(false)
            ->setAddDate(new \DateTime())
            ->setToken($token);

        $subscription = $this->backInStockSubscriptionRepository->save($subscription);

        return $subscription;
    }

    protected function sendConfirmationEmail(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription, \Magento\Catalog\Api\Data\ProductInterface $product, array $params, int $storeId, int $customerId): void //phpcs:ignore
    {
        if (!$this->configuration->isConfirmationRequired()) {
            return;
        }

        $this->setTemplateParams($subscription, $product);

        $this->sendConfirmationRequest(
            $params['email'],
            $this->templateParams,
            \MageSuite\BackInStock\Helper\Configuration::CONFIRMATION_EMAIL_CONFIG_PATH,
            $storeId,
            $customerId
        );
    }
}
