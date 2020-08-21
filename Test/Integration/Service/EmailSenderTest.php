<?php
namespace MageSuite\BackInStock\Test\Integration\Service;

class EmailSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \MageSuite\BackInStock\Service\EmailSender
     */
    protected $emailSender;

    /**
     * @var \Magento\TestFramework\Mail\Template\TransportBuilderMock|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $transportBuilder;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);

        $this->emailSender = $this->objectManager->create(\MageSuite\BackInStock\Service\EmailSender::class, ['transportBuilder' => $this->objectManager->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class)]);
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/product_simple.php
     */
    public function testItSendEmailCorrectly()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get('simple');

        /** @var  \MageSuite\BackInStock\Service\EmailSender $emailSender */
        $emailSender = $this->emailSender;

        $emailSender->sendMail('test@test.com', ['notification_message' => 'test message'], 'back_in_stock/email_configuration/manual_notification_email_template', 1);

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);

        $sentMessage = $transportBuilder->getSentMessage()->getRawMessage();

        $this->assertContains('test@test.com', $sentMessage);

        $emailSender->sendMail('test@test.com', ['product_name' => $product->getName(), 'product_sku' => $product->getSku(), 'product_url' => $product->getProductUrl()], 'back_in_stock/email_configuration/automatic_notification_email_template', 1);

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);

        $sentMessage = $transportBuilder->getSentMessage()->getRawMessage();

        $this->assertContains('test@test.com', $sentMessage);
    }
}
