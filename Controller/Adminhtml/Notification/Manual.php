<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Manual extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_BackInStock::config_backinstock';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        protected \MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator
    ) {
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute(): \Magento\Framework\Controller\Result\Json
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if (empty($data)) {
            return $result;
        }

        try {
            foreach ($data['messages'] as $storeId => $message) {
                if (empty($message)) {
                    continue;
                }
                $this->notificationQueueCreator->addNotificationsToQueue((int) $data['product_id'], $storeId, \MageSuite\BackInStock\Service\NotificationQueueSender::MANUAL_NOTIFICATION, $message);
            }

            $this->messageManager->addSuccessMessage(__('Customers have been notified.'));
            $result->setData(['success' => true]);
        } catch (\Exception $e) {
            $result->setData([
                'success' => false,
                'errorMessage' => __('Error occured while sending notifications.', $e->getMessage())
            ]);
        }

        return $result;
    }
}
