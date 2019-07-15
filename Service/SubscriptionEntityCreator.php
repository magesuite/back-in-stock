<?php
namespace MageSuite\BackInStock\Service;

class SubscriptionEntityCreator
{
    const CONFIRMATION_EMAIL_CONFIG_PATH = 'back_in_stock/email_configuration/confirmation_email_template';

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
     * @var EmailSender
     */
    protected $emailSender;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $url;

    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MageSuite\BackInStock\Model\BackInStockSubscription $backInStockSubscription,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \MageSuite\BackInStock\Api\BackInStockSubscriptionRepositoryInterface $backInStockSubscriptionRepository,
        \MageSuite\BackInStock\Service\EmailSender $emailSender,
        \Magento\Framework\UrlInterface $url
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
    }

    public function subscribe($params)
    {
        $storeId = $this->storeManager->getStore()->getId();
        $customerSession = $this->customerSession->create();
        $customerId = $customerSession->getCustomerId() ?? 0;

        $productId = $params['product_id'];
        $email = $params['email'];

        if(!$this->validateEmail($email)){
            throw new \Exception(__('Invalid email address.'));
        }

        if(!$this->validateEntityExist($productId, $customerId, $email, $storeId)){
            throw new \Magento\Framework\Exception\AlreadyExistsException(__('You have already subscribed for a back-to-stock notification for this product.'));
        }

        $product = $this->productRepository->getById($productId);

        $token = $this->backInStockSubscriptionRepository->generateToken($email, $customerId);

        $subscription = $this->backInStockSubscription
            ->setCustomerId($customerId)
            ->setCustomerEmail($email)
            ->setProductId($product->getId())
            ->setStoreId($storeId)
            ->setToken($token);

        $subscription = $this->backInStockSubscriptionRepository->save($subscription);

        $this->setTemplateParams($subscription, $product);

        $this->sendConfirmationRequest($params['email'], $this->templateParams, self::CONFIRMATION_EMAIL_CONFIG_PATH, $storeId, $customerId);

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
        $this->emailSender->sendMail($email, $params, $templateConfigPath, $storeId);
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
        if(!\Zend_Validate::is(trim($email), 'EmailAddress')){
            return false;
        }

        return true;
    }

    public function validateEntityExist($productId, $customerId, $email, $storeId)
    {
        if($this->backInStockSubscriptionRepository->subscriptionExist($productId, $customerId, $email, $storeId)){
            return false;
        }

        return true;
    }
}