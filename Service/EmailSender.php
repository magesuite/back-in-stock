<?php
namespace MageSuite\BackInStock\Service;

class EmailSender
{
    /**
     * Store manager
     *
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
    private $configuration;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Psr\Log\LoggerInterface $logger,
        \MageSuite\BackInStock\Helper\Configuration $configuration
    ) {
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->configuration = $configuration;
    }

    public function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo, $storeId)
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

    public function sendMail($receiverEmail, $emailTemplateVariables, $templateConfigPath, $storeId)
    {
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
        }
    }

}