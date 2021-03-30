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
        } catch (\Magento\Framework\Exception\AlreadyExistsException $e) {
            return $jsonResult->setData([
                'success' => false,
                'message' => __('Subscription already exist.')
            ]);
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
            'message' => __('Subscription has been saved. We will notify you when product is back in stock.')
        ]);
    }
}
