<?php
namespace MageSuite\BackInStock\Helper;

class Configuration
{
    const MODULE_ENABLED_CONFIG_PATH = 'back_in_stock/general/enabled';
    const SENDER_TYPE_CONFIG_PATH = 'back_in_stock/email_configuration/sender_email';
    const SENDER_NAME_CONFIG_PATH = 'trans_email/ident_%s/name';
    const SENDER_EMAIL_CONFIG_PATH = 'trans_email/ident_%s/email';
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

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
        return (bool) $this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH);
    }

    public function canDisplaySubscriptionForm($product, $storeId)
    {
        return (bool) $this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH, $storeId) && $product && !$product->isAvailable();
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
