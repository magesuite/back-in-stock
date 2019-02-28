<?php
namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Preview extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \MageSuite\BackInStock\Service\PreviewNotificationSender
     */
    protected $previewNotificationSender;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \MageSuite\BackInStock\Service\PreviewNotificationSender $previewNotificationSender
    )
    {
        parent::__construct($context);

        $this->resultJsonFactory = $resultJsonFactory;
        $this->previewNotificationSender = $previewNotificationSender;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        /** @var \Magento\Framework\Controller\Result\Json $result */
        $result = $this->resultJsonFactory->create();

        if ($data) {
            try {
                foreach ($data['messages'] as $storeId => $message) {
                    if(empty($message)){
                        continue;
                    }
                    $this->previewNotificationSender->sendPreview($data['preview_email_address'], $storeId, $message);
                }

                $result->setData([
                    'success' => true,
                    'successMessage' => __('Preview emails has been sent to provided email address.')
                ]);
            } catch (\Exception $e) {

                $result->setData([
                    'success' => false,
                    'errorMessage' => __('Error occured while sending notifications.', $e->getMessage())
                ]);
            }
        }

        return $result;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
