<?php
namespace MageSuite\BackInStock\Helper;

class Configuration
{
    const MODULE_ENABLED_CONFIG_PATH = 'back_in_stock/general/enabled';
    const AUTOMATIC_REMOVE_SUBSCRIPTION_CONFIG_PATH = 'back_in_stock/general/remove_subscription_after_send_notification';
    const IS_CONFIRMATION_REQUIRED_CONFIG_PATH = 'back_in_stock/general/is_confirmation_required';
    const SUCCESS_WITH_CONFIRMATION_MESSAGE_CONFIG_PATH = 'back_in_stock/general/success_with_confirmation_message';
    const SUCCESS_WITHOUT_CONFIRMATION_MESSAGE_CONFIG_PATH = 'back_in_stock/general/success_without_confirmation_message';
    const SENDER_TYPE_CONFIG_PATH = 'back_in_stock/email_configuration/sender_email';
    const SENDER_NAME_CONFIG_PATH = 'trans_email/ident_%s/name';
    const SENDER_EMAIL_CONFIG_PATH = 'trans_email/ident_%s/email';
    const CONFIRMATION_EMAIL_CONFIG_PATH = 'back_in_stock/email_configuration/confirmation_email_template';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getConfigValue($path, $storeId = \Magento\Store\Model\Store::DEFAULT_STORE_ID)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isModuleEnabled()
    {
        return (bool)$this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH);
    }

    public function isRemoveSubscriptionAfterSendNotification()
    {
        return (bool)$this->getConfigValue(self::AUTOMATIC_REMOVE_SUBSCRIPTION_CONFIG_PATH);
    }

    public function isConfirmationRequired()
    {
        return (bool)$this->getConfigValue(self::IS_CONFIRMATION_REQUIRED_CONFIG_PATH);
    }

    public function getSuccessSubscribeMessage($storeId = null)
    {
        if ($this->isConfirmationRequired()) {
            return $this->getSuccessWithConfirmationMessage($storeId);
        }

        return $this->getSuccessWithoutConfirmationMessage($storeId);
    }

    public function getSuccessWithConfirmationMessage($storeId = null)
    {
        return $this->getConfigValue(self::SUCCESS_WITH_CONFIRMATION_MESSAGE_CONFIG_PATH, $storeId);
    }

    public function getSuccessWithoutConfirmationMessage($storeId = null)
    {
        return $this->getConfigValue(self::SUCCESS_WITHOUT_CONFIRMATION_MESSAGE_CONFIG_PATH, $storeId);
    }

    public function canDisplaySubscriptionForm($storeId)
    {
        return (bool)$this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH, $storeId);
    }

    public function getEmailTemplateId($templateConfigPath, $storeId)
    {
        return $this->getConfigValue($templateConfigPath, $storeId);
    }

    public function getEmailSenderData($storeId)
    {
        $emailSenderValue = $this->getConfigValue(self::SENDER_TYPE_CONFIG_PATH, $storeId);
        return [
            'name' => $this->getConfigValue(sprintf(self::SENDER_NAME_CONFIG_PATH, $emailSenderValue), $storeId),
            'email' => $this->getConfigValue(sprintf(self::SENDER_EMAIL_CONFIG_PATH, $emailSenderValue), $storeId),
        ];
    }
}
