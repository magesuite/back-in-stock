<?php

namespace MageSuite\BackInStock\Service;

class EmailSender
{
    const STATUS_SENT = 'sent';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var string
     */
    protected $templateId;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \MageSuite\BackInStock\Helper\Configuration
     */
    protected $configuration;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    protected $customerRepository;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\BackInStock\Helper\Configuration $configuration,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->configuration = $configuration;
        $this->customerRepository = $customerRepository;
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
        if ($customerId) {
            $this->addCustomerNameToVariables($emailTemplateVariables, $customerId);
        }

        try {
            $this->templateId = $this->configuration->getEmailTemplateId($templateConfigPath, $storeId);

            $this->inlineTranslation->suspend();

            $this->generateTemplate(
                $emailTemplateVariables,
                $this->configuration->getEmailSenderData($storeId),
                ['email' => $receiverEmail, 'name' => 'customer'],
                $storeId
            );
            $transport = $this->transportBuilder->getTransport();

            $transport->sendMessage();

            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return $e->getMessage();
        }

        return self::STATUS_SENT;
    }

    protected function addCustomerNameToVariables(&$emailTemplateVariables, $customerId)
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (\Exception $e) {
            $emailTemplateVariables['customerName'] = null;
            return;
        }

        $emailTemplateVariables['customerName'] = sprintf('%s %s', $customer->getFirstname(), $customer->getLastname());
    }
}
