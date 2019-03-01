<?php
namespace MageSuite\BackInStock\Test\Integration\Service;

class PreviewNotificationSenderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \MageSuite\BackInStock\Service\PreviewNotificationSender
     */
    protected $previewNotificationSender;
    /**
     * @var \MageSuite\BackInStock\Service\EmailSender
     */
    protected $emailSender;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->previewNotificationSender = $this->objectManager->create(\MageSuite\BackInStock\Service\PreviewNotificationSender::class, ['emailSender' => $this->objectManager->create(\MageSuite\BackInStock\Service\EmailSender::class, ['transportBuilder' => $this->objectManager->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class)])]);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testItSendPreviewEmailCorrectly()
    {
        $this->previewNotificationSender->sendPreview('test@preview.com', 0, 'Test preview message');

        /** @var \Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder */
        $transportBuilder = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);

        $sentMessage = $transportBuilder->getSentMessage()->getRawMessage();

        $this->assertContains('test@preview.com', $sentMessage);
    }

}