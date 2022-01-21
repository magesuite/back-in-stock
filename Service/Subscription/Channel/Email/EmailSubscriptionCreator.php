<?php

namespace MageSuite\BackInStock\Service\Subscription\Channel\Email;

class EmailSubscriptionCreator
{

    protected $customer = null;

    protected $templateParams = [];

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    protected $backInStockSubscription;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface
     */
    protected $backInStockSubscriptionRepository;

    /**
     * @var \MageSuite\BackInStock\Service\EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    /**
     * @var \MageSuite\BackInStock\Service\Subscription\ProductResolver
     */
    protected $productResolver;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \MageSuite\BackInStock\Model\BackInStockSubscriptionFactory
     */
    protected $backInStockSubscriptionFactory;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Service\EmailSender $emailSender,
        \Magento\Framework\UrlInterface $url,
        \MageSuite\BackInStock\Service\Subscription\ProductResolver $productResolver,
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \MageSuite\BackInStock\Model\BackInStockSubscriptionFactory $backInStockSubscriptionFactory
    ) {
        $this->productRepository = $productRepository;
        $this->customerSession = $customerSession;
        $this->messageManager = $messageManager;
        $this->backInStockSubscription = $backInStockSubscription;
        $this->storeManager = $storeManager;
        $this->backInStockSubscriptionRepository = $backInStockSubscriptionRepository;
        $this->emailSender = $emailSender;
        $this->url = $url;
        $this->productResolver = $productResolver;
        $this->configuration = $configuration;
        $this->backInStockSubscriptionFactory = $backInStockSubscriptionFactory;
    }

    public function subscribe($params)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $customerSession = $this->customerSession->create();
        $customerId = $customerSession->getCustomerId() ?? 0;

        $email = $params['email'];

        $product = $this->productResolver->resolve($params);

        if (!$this->validateEmail($email)) {
            throw new \Exception(__('Invalid email address.')); //phpcs:ignore
        }

        if ($this->subscriptionExist($product->getId(), $customerId, $email, $storeId)) {
            $subscription = $this->getExistingSubscription($product->getId(), $customerId, $email, $storeId);
            if (!$this->canSubscriptionBeReset($subscription)) {
                return;
            }
            $subscription = $this->resetExistingSubscription($subscription, $customerId, $email);
        } else {
            $subscription = $this->createNewSubscription($product, $customerId, $email, $storeId);
        }

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

    public function getCustomer()
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

    public function setTemplateParams($subscription, $product)
    {
        $this->templateParams = [
            'email' => $subscription->getCustomerEmail(),
            'product' => $product,
            'customer_id' => $subscription->getCustomerId(),
            'confirm_url' => $this->getConfirmUrl($subscription),
            'unsubscribe_url' => $this->getUnsubscribeUrl($subscription)
        ];
    }

    public function getConfirmUrl($subscription)
    {
        return $this->url->getUrl('backinstock/notification/confirm', ['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
    }

    public function getUnsubscribeUrl($subscription)
    {
        return $this->url->getUrl('backinstock/notification/unsubscribe', ['id' => $subscription->getId(), 'token' => $subscription->getToken()]);
    }

    public function validateEmail($email)
    {
        return \Zend_Validate::is(trim($email), 'EmailAddress');
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

    /**
     * @param int $productId
     * @param int $customerId
     * @param string $email
     * @param int $storeId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
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

    /**
     * @param \MageSuite\BackInStock\Model\BackInStockSubscription $subscription
     * @return bool
     * @throws \Exception
     */
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

    /**
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param int $customerId
     * @param string $email
     * @param int $storeId
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
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

    /**
     * @param \MageSuite\BackInStock\Model\BackInStockSubscription $subscription
     * @param int $customerId
     * @param string $email
     * @return \MageSuite\BackInStock\Model\BackInStockSubscription
     */
    public function resetExistingSubscription(\MageSuite\BackInStock\Model\BackInStockSubscription $subscription, int $customerId, string $email): \MageSuite\BackInStock\Model\BackInStockSubscription
    {
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
}
