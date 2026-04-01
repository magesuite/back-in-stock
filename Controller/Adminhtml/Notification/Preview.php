<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Controller\Adminhtml\Notification;

class Preview extends \Magento\Backend\App\Action implements \Magento\Framework\App\Action\HttpPostActionInterface
{
    public const ADMIN_RESOURCE = 'MageSuite_BackInStock::config_backinstock';

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        protected \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        protected \MageSuite\BackInStock\Service\PreviewNotificationSender $previewNotificationSender
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

        return $result;
    }
}
