<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Controller\Notification;

class Subscribe extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        protected \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        protected \MageSuite\BackInStock\Service\SubscriptionEntityCreator $subscriptionEntityCreator,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \Magento\Store\Model\StoreManager $storeManager,
        protected \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    public function verifyAllAttributesAreSelected(array $params): bool
    {
        if (!isset($params['super_attribute']) || !is_array($params['super_attribute'])) {
            return true;
        }

        foreach ($params['super_attribute'] as $attribute) {
            if (empty($attribute)) {
                return false;
            }
        }

        return true;
    }

    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $params = $this->_request->getParams();

        $jsonResult = $this->jsonResultFactory->create();
        $storeId = (int)$this->storeManager->getStore()->getId();

        if (!$this->configuration->canDisplaySubscriptionForm($storeId)) {
            return $jsonResult->setData([
                'success' => false,
                'message' => __('Back in stock notifications are not available.')
            ]);
        }

        if (!$this->verifyAllAttributesAreSelected($params)) {
            return $jsonResult->setData([
                'success' => false,
                'message' => __('Please, select all product attributes.')
            ]);
        }

        try {
            $this->subscriptionEntityCreator->subscribe($params);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            return $jsonResult->setData([
                'success' => false,
                'message' => $e->getMessage()
            ]);
        } catch (\Exception $e) {
            $this->logger->error($e);

            return $jsonResult
                ->setHttpResponseCode(500)
                ->setData([
                    'success' => false,
                    'message' => __('Something went wrong. Please try again later.')
                ]);
        }

        $successMessage = $this->configuration->getSuccessSubscribeMessage($storeId);

        return $jsonResult->setData([
            'success' => true,
            'message' => __($successMessage, \MageSuite\BackInStock\Model\BackInStockSubscription::SUBSCRIPTION_CONFIRMATION_AWAITING_TIME_IN_HOURS)
        ]);
    }
}
