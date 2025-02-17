<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Helper;

class Configuration
{
    public const MODULE_ENABLED_CONFIG_PATH = 'back_in_stock/general/enabled';
    public const AUTOMATIC_REMOVE_SUBSCRIPTION_CONFIG_PATH = 'back_in_stock/general/remove_subscription_after_send_notification';
    public const IS_HISTORICAL_DATA_KEPT_CONFIG_PATH = 'back_in_stock/general/is_historical_data_kept';
    public const IS_CONFIRMATION_REQUIRED_CONFIG_PATH = 'back_in_stock/general/is_confirmation_required';
    public const SUCCESS_WITH_CONFIRMATION_MESSAGE_CONFIG_PATH = 'back_in_stock/general/success_with_confirmation_message';
    public const SUCCESS_WITHOUT_CONFIRMATION_MESSAGE_CONFIG_PATH = 'back_in_stock/general/success_without_confirmation_message';
    public const SENDER_TYPE_CONFIG_PATH = 'back_in_stock/email_configuration/sender_email';
    public const SENDER_NAME_CONFIG_PATH = 'trans_email/ident_%s/name';
    public const SENDER_EMAIL_CONFIG_PATH = 'trans_email/ident_%s/email';
    public const CONFIRMATION_EMAIL_CONFIG_PATH = 'back_in_stock/email_configuration/confirmation_email_template';
    public const NOTIFICATION_LIMITS_MIN_TIME_CONFIG_PATH = 'back_in_stock/limits/min_time';
    public const NOTIFICATION_LIMITS_DAILY_LIMIT_CONFIG_PATH = 'back_in_stock/limits/daily_limit';
    public const NOTIFICATION_LIMITS_MAX_AGE_LIMIT_CONFIG_PATH = 'back_in_stock/limits/max_age';

    public function __construct(protected \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
    }

    public function getConfigValue($path, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isModuleEnabled(): bool
    {
        return (bool)$this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH);
    }

    public function isRemoveSubscriptionAfterSendNotification(): bool
    {
        return (bool)$this->getConfigValue(self::AUTOMATIC_REMOVE_SUBSCRIPTION_CONFIG_PATH);
    }

    public function isHistoricalDataKept(): bool
    {
        return (bool)$this->getConfigValue(self::IS_HISTORICAL_DATA_KEPT_CONFIG_PATH);
    }

    public function isConfirmationRequired(): bool
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

    public function canDisplaySubscriptionForm($storeId): bool
    {
        return (bool)$this->getConfigValue(self::MODULE_ENABLED_CONFIG_PATH, $storeId);
    }

    public function getEmailTemplateId($templateConfigPath, $storeId)
    {
        return $this->getConfigValue($templateConfigPath, $storeId);
    }

    public function getEmailSenderData($storeId): array
    {
        $emailSenderValue = $this->getConfigValue(self::SENDER_TYPE_CONFIG_PATH, $storeId);

        return [
            'name' => $this->getConfigValue(sprintf(self::SENDER_NAME_CONFIG_PATH, $emailSenderValue), $storeId),
            'email' => $this->getConfigValue(sprintf(self::SENDER_EMAIL_CONFIG_PATH, $emailSenderValue), $storeId),
        ];
    }

    public function getMinTimeBetweenNotifications(): int
    {
        return (int)$this->getConfigValue(self::NOTIFICATION_LIMITS_MIN_TIME_CONFIG_PATH);
    }

    public function getDailyNotificationLimit(): int
    {
        return (int)$this->getConfigValue(self::NOTIFICATION_LIMITS_DAILY_LIMIT_CONFIG_PATH);
    }
}
