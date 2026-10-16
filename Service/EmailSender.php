<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Service;

class EmailSender
{
    public const STATUS_SENT = 'sent';

    protected ?string $templateId = null;

    public function __construct(
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        protected \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        protected \Psr\Log\LoggerInterface $logger,
        protected \MageSuite\BackInStock\Helper\Configuration $configuration,
        protected \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
    }

    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId) //phpcs:ignore
    {
        $this->transportBuilder->setTemplateIdentifier($this->templateId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $storeId,
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo)
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this->transportBuilder;
    }

    public function sendMail($receiverEmail, $emailTemplateVariables, $templateConfigPath, $storeId, $customerId = 0) //phpcs:ignore
    {
        $receiverName = $receiverEmail;

        if ($customerId) {
            $emailTemplateVariables = $this->addCustomerNameToVariables($emailTemplateVariables, (int)$customerId, (string)$receiverEmail);
            $receiverName = $emailTemplateVariables['customerName'] ?? $receiverEmail;
        }

        $this->inlineTranslation->suspend();

        try {
            $templateId = $this->configuration->getEmailTemplateId($templateConfigPath, $storeId);
            $this->templateId = $templateId === null ? null : (string)$templateId;

            $this->generateTemplate(
                $emailTemplateVariables,
                $this->configuration->getEmailSenderData($storeId),
                ['email' => $receiverEmail, 'name' => $receiverName],
                $storeId
            );
            $transport = $this->transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());

            return $e->getMessage();
        } finally {
            $this->inlineTranslation->resume();
        }

        return self::STATUS_SENT;
    }

    public function addCustomerNameToVariables(array $emailTemplateVariables, int $customerId, string $receiverEmail): array
    {
        $emailTemplateVariables['customerName'] = null;

        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            $this->logger->error($e);

            return $emailTemplateVariables;
        }

        if (!$this->isCustomerOwnAddress($customer, $receiverEmail)) {
            return $emailTemplateVariables;
        }

        $emailTemplateVariables['customerName'] = sprintf('%s %s', $customer->getFirstname(), $customer->getLastname());

        return $emailTemplateVariables;
    }

    protected function isCustomerOwnAddress(\Magento\Customer\Api\Data\CustomerInterface $customer, string $receiverEmail): bool
    {
        return strcasecmp((string)$customer->getEmail(), trim($receiverEmail)) === 0;
    }
}
