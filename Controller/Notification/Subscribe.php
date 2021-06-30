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


    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \MageSuite\BackInStock\Service\SubscriptionEntityCreator $subscriptionEntityCreator
    )
    {
        parent::__construct($context);

        $this->subscriptionEntityCreator = $subscriptionEntityCreator;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    public function execute()
    {
        $params = $this->_request->getParams();

        $jsonResult = $this->jsonResultFactory->create();

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

        return $jsonResult->setData([
            'success' => true,
            'message' => __('If you have not subscribed to this product yet, a request to confirm your subscription will be sent to your email. If you have subscribed to this product in the last %1 hours, check your email - the confirmation request should already be there.', \MageSuite\BackInStock\Model\BackInStockSubscription::SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS)
        ]);
    }
}
