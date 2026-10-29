<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Integration\Service\Subscription\Channel\Email;

#[\Magento\TestFramework\Fixture\AppArea('frontend')]
class EmailSubscriptionCreatorTest extends \PHPUnit\Framework\TestCase
{
    public const string SUBSCRIBER_EMAIL = 'back-in-stock@example.com';

    protected ?\Magento\TestFramework\ObjectManager $objectManager = null;
    protected ?\Magento\TestFramework\Mail\Template\TransportBuilderMock $transportBuilder = null;

    protected function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->transportBuilder = $this->objectManager->get(\Magento\TestFramework\Mail\Template\TransportBuilderMock::class);
        $this->transportBuilder->clean();
    }

    #[\Magento\TestFramework\Fixture\Config('back_in_stock/general/is_confirmation_required', 0, 'store', 'admin')]
    #[\Magento\TestFramework\Fixture\Config('back_in_stock/email_configuration/is_activation_email_enabled', 1, 'store', 'default')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['name' => 'Gold Bar 100g'], 'product')]
    public function testSubscribeSendsActivationEmailWithProductWhenConfirmationIsNotRequired(): void
    {
        $productId = (int)\Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage()->get('product')->getId();

        $this->subscribe($productId);

        $body = quoted_printable_decode($this->transportBuilder->getSentMessage()->getBody()->bodyToString());

        $this->assertTrue((bool)$this->getSubscription($productId)->getCustomerConfirmed());
        $this->assertStringContainsString('Gold Bar 100g', $body);
        $this->assertStringContainsString('backinstock/notification/unsubscribe', $body);
        $this->assertStringNotContainsString('backinstock/notification/confirm', $body);
    }

    #[\Magento\TestFramework\Fixture\Config('back_in_stock/general/is_confirmation_required', 0, 'store', 'admin')]
    #[\Magento\TestFramework\Fixture\Config('back_in_stock/email_configuration/is_activation_email_enabled', 0, 'store', 'default')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, as: 'product')]
    public function testSubscribeSendsNoEmailWhenActivationEmailIsDisabled(): void
    {
        $this->subscribe((int)\Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage()->get('product')->getId());

        $this->assertNull($this->transportBuilder->getSentMessage());
    }

    #[\Magento\TestFramework\Fixture\Config('back_in_stock/general/is_confirmation_required', 1, 'store', 'admin')]
    #[\Magento\TestFramework\Fixture\Config('back_in_stock/email_configuration/is_activation_email_enabled', 1, 'store', 'default')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, as: 'product')]
    public function testSubscribeSendsConfirmationInsteadOfActivationEmailWhenConfirmationIsRequired(): void
    {
        $productId = (int)\Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage()->get('product')->getId();

        $this->subscribe($productId);

        $body = quoted_printable_decode($this->transportBuilder->getSentMessage()->getBody()->bodyToString());

        $this->assertFalse((bool)$this->getSubscription($productId)->getCustomerConfirmed());
        $this->assertStringContainsString('backinstock/notification/confirm', $body);
    }

    #[\Magento\TestFramework\Fixture\Config('back_in_stock/email_configuration/is_activation_email_enabled', 1, 'store', 'default')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['name' => 'Coin Parent', 'url_key' => 'coin-parent'], 'parent_product')]
    #[\Magento\TestFramework\Fixture\DataFixture(\Magento\Catalog\Test\Fixture\Product::class, ['name' => 'Coin Child', 'url_key' => 'coin-child', 'visibility' => 1], 'child_product')]
    public function testActivationEmailLinksToParentProductOfChildSubscription(): void
    {
        $fixtures = \Magento\TestFramework\Fixture\DataFixtureStorageManager::getStorage();
        $subscription = $this->objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class);
        $subscription->setProductId($fixtures->get('child_product')->getId());
        $subscription->setParentProductId($fixtures->get('parent_product')->getId());
        $subscription->setCustomerEmail(self::SUBSCRIBER_EMAIL);
        $subscription->setStoreId(1);

        $this->objectManager->create(\MageSuite\BackInStock\Service\Subscription\Channel\Email\ActivationEmailSender::class)->send($subscription);

        $body = quoted_printable_decode($this->transportBuilder->getSentMessage()->getBody()->bodyToString());

        $this->assertStringContainsString('Coin Parent', $body);
        $this->assertStringContainsString('coin-parent', $body);
        $this->assertStringNotContainsString('coin-child', $body);
    }

    protected function subscribe(int $productId): void
    {
        $emailSubscriptionCreator = $this->objectManager->create(\MageSuite\BackInStock\Service\Subscription\Channel\Email\EmailSubscriptionCreator::class, [
            'backInStockSubscription' => $this->objectManager->create(\MageSuite\BackInStock\Model\BackInStockSubscription::class),
        ]);

        $emailSubscriptionCreator->subscribe(['notification_channel' => 'email', 'product' => $productId, 'email' => self::SUBSCRIBER_EMAIL]);
    }

    protected function getSubscription(int $productId): \MageSuite\BackInStock\Model\BackInStockSubscription
    {
        $subscriptionCollection = $this->objectManager->create(\MageSuite\BackInStock\Model\ResourceModel\BackInStockSubscription\Collection::class);
        $subscriptionCollection->addFieldToFilter('product_id', $productId);
        $subscriptionCollection->addFieldToFilter('customer_email', self::SUBSCRIBER_EMAIL);

        return $subscriptionCollection->getFirstItem();
    }
}
