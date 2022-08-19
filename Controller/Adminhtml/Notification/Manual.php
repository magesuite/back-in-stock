<?php

namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Manual extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var \MageSuite\BackInStock\Service\NotificationQueueCreator
     */
    protected $notificationQueueCreator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MageSuite\BackInStock\Service\NotificationQueueCreator $notificationQueueCreator
    ) {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->notificationQueueCreator = $notificationQueueCreator;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
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

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
