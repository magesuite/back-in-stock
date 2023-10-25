<?php

namespace MageSuite\BackInStock\Controller\Notification;

class Subscribe extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $jsonResultFactory;

    /**
     * @var \MageSuite\BackInStock\Service\SubscriptionEntityCreator
     */
    protected $subscriptionEntityCreator;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Store\Model\StoreManager
     */
    protected $storeManager;

    public function __construct(
        \Magento\Framework\App\Action\Context                    $context,
        \Magento\Framework\Controller\Result\JsonFactory         $jsonResultFactory,
        \MageSuite\BackInStock\Service\SubscriptionEntityCreator $subscriptionEntityCreator,
        \MageSuite\BackInStock\Helper\Configuration              $configuration,
        \Magento\Store\Model\StoreManager                        $storeManager
    ) {
        parent::__construct($context);
        $this->subscriptionEntityCreator = $subscriptionEntityCreator;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->configuration = $configuration;
        $this->storeManager = $storeManager;
    }

    public function verifyAllAttributesAreSelected(array $params)
    {
        if (isset($params['super_attribute']) && is_array($params['super_attribute'])) {
            foreach ($params['super_attribute'] as $attribute) {
                if (empty($attribute)) {
                    return false;
                }
            }
        }

        return true;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $jsonResult = $this->jsonResultFactory->create();

        if (!$this->verifyAllAttributesAreSelected($params)) {
            return $jsonResult->setData([
                'success' => false,
                'message' => __('Please, select all product attributes.')
            ]);
        }

        try {
            $this->subscriptionEntityCreator->subscribe($params);
        } catch (\Exception $e) {
            return $jsonResult
                ->setHttpResponseCode(500)
                ->setData([
                    'success' => false,
                    'message' => $e->getMessage()
                ]);
        }

        $successMessage = $this->configuration->getSuccessSubscribeMessage($this->storeManager->getStore()->getId());

        return $jsonResult->setData([
            'success' => true,
            'message' => __($successMessage, \MageSuite\BackInStock\Model\BackInStockSubscription::SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS)
        ]);
    }
}
