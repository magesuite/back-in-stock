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
        \MageSuite\BackInStock\Helper\Configuration $configuration
    )
    {
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
    }

    public function subscribe($params)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $customerSession = $this->customerSession->create();
        $customerId = $customerSession->getCustomerId() ?? 0;

        $email = $params['email'];

        $product = $this->productResolver->resolve($params);

        if(!$this->validateEmail($email)){
            throw new \Exception(__('Invalid email address.'));
        }

        if($this->subscriptionExist($product->getId(), $customerId, $email, $storeId)){
            throw new \Magento\Framework\Exception\AlreadyExistsException(__('You have already subscribed for a back-to-stock notification for this product.'));
        }

        $token = $this->backInStockSubscriptionRepository->generateToken($email, $customerId);

        $subscription = $this->backInStockSubscription
            ->setCustomerId($customerId)
            ->setCustomerEmail($email)
            ->setProductId($product->getId())
            ->setParentProductId($product->getParentProductId())
            ->setStoreId($storeId)
            ->setNotificationChannel('email')
            ->setToken($token);

        $subscription = $this->backInStockSubscriptionRepository->save($subscription);

        $this->setTemplateParams($subscription, $product);

        $this->sendConfirmationRequest(
            $params['email'],
            $this->templateParams,
            \MageSuite\BackInStock\Helper\Configuration::CONFIRMATION_EMAIL_CONFIG_PATH,
            $storeId,
            $customerId
        );

        $this->messageManager->addSuccessMessage(__('Alert subscription has been saved. You will receive email with confirmation.'));
    }

    public function getCustomer()
    {
        if(!$this->customer){
            $this->customer = $this->customerSession->getCustomer();
        }

        return $this->customer;
    }

    public function sendConfirmationRequest($email, $params, $templateConfigPath, $storeId, $customerId)
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

    public function subscriptionExist($productId, $customerId, $email, $storeId)
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
}
