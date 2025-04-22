<?php

declare(strict_types=1);

namespace MageSuite\BackInStock\Test\Unit;

class LimitationValidatorTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\App\ObjectManager $objectManager = null;
    protected ?\MageSuite\BackInStock\Model\LimitationValidator $limitationValidator = null;
    protected ?\MageSuite\BackInStock\Helper\Configuration $configurationMock = null;
    protected ?\Magento\Framework\Stdlib\DateTime\DateTime $dateTimeMock = null;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $allowedMethodName = method_exists(\PHPUnit\Framework\MockObject\MockBuilder::class, 'onlyMethods')
            ? 'onlyMethods'
            : 'setMethods';

        $this->dateTimeMock = $this->getMockBuilder(\Magento\Framework\Stdlib\DateTime\DateTime::class)
            ->disableOriginalConstructor()
            ->$allowedMethodName(['gmtTimestamp'])
            ->getMock();

        $this->configurationMock = $this->getMockBuilder(\MageSuite\BackInStock\Helper\Configuration::class)
            ->disableOriginalConstructor()
            ->$allowedMethodName(['getMinTimeBetweenNotifications', 'getDailyNotificationLimit'])
            ->getMock();

        $this->limitationValidator = $this->objectManager->create(
            \MageSuite\BackInStock\Model\LimitationValidator::class,
            [
                'dateTime' => $this->dateTimeMock,
                'config' => $this->configurationMock
            ]
        );
    }

    /**
     * @dataProvider getMinTimeDataProvider
     */
    public function testMinTimeValid(?string $sendDate, int $sendCount, ?int $minTime, string $currentTime, bool $expected): void
    {
        /**
         * @var \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscriptionMock
         */
        $subscriptionMock = $this->getSubscriptionMock($sendDate, $sendCount);
        $this->configurationMock->method('getMinTimeBetweenNotifications')->willReturn($minTime);
        $this->dateTimeMock->method('gmtTimestamp')->willReturn(strtotime($currentTime));

        $this->assertEquals($expected, $this->limitationValidator->isMinTimeValid($subscriptionMock));
    }

    /**
     * @dataProvider getDailyLimitDataProvider
     */
    public function testDailyLimitExceeded(?string $sendDate, int $sendDailyCount, ?int $dailyLimit, string $currentTime, bool $expected): void
    {
        /**
         * @var \MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface $subscriptionMock
         */
        $subscriptionMock = $this->getSubscriptionMock(sendDate: $sendDate, sendDailyCount: $sendDailyCount);
        $this->configurationMock->method('getDailyNotificationLimit')->willReturn($dailyLimit);
        $this->dateTimeMock->method('gmtTimestamp')->willReturn(strtotime($currentTime));

        $this->assertEquals($expected, $this->limitationValidator->isDailyLimitExceeded($subscriptionMock));
    }

    protected function getSubscriptionMock(?string $sendDate, int $sendCount = 0, int $sendDailyCount = 0): \PHPUnit\Framework\MockObject\MockObject|\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface
    {
        $subscriptionMock = $this->getMockBuilder(\MageSuite\BackInStock\Api\Data\BackInStockSubscriptionInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $subscriptionMock->method('getSendDate')->willReturn($sendDate);
        $subscriptionMock->method('getSendCount')->willReturn($sendCount);
        $subscriptionMock->method('getSendCountDaily')->willReturn($sendDailyCount);

        return $subscriptionMock;
    }

    public static function getMinTimeDataProvider(): array
    {
        return [
            'min_time_not_set' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendCount' => 0,
                'minTime' => 0,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => true
            ],
            'last_send_not_set' => [
                'sendDate' => null,
                'sendCount' => 0,
                'minTime' => 3600,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => true
            ],
            'min_time_not_passed' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendCount' => 0,
                'minTime' => 3600,
                'currentTime' => '2025-01-01 00:30:00',
                'expected' => false
            ],
            'min_time_passed' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendCount' => 0,
                'minTime' => 3600,
                'currentTime' => '2025-01-01 01:00:00',
                'expected' => true
            ]
        ];
    }

    public static function getDailyLimitDataProvider(): array
    {
        return [
            'daily_limit_not_set' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendDailyCount' => 0,
                'dailyLimit' => 0,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => false
            ],
            'last_send_not_set' => [
                'sendDate' => null,
                'sendDailyCount' => 0,
                'dailyLimit' => 3,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => false
            ],
            'daily_limit_not_exceeded' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendDailyCount' => 2,
                'dailyLimit' => 3,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => false
            ],
            'daily_limit_exceeded' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendDailyCount' => 3,
                'dailyLimit' => 3,
                'currentTime' => '2025-01-01 00:00:00',
                'expected' => true
            ],
            'daily_limit_not_exceeded_next_day' => [
                'sendDate' => '2025-01-01 00:00:00',
                'sendDailyCount' => 4,
                'dailyLimit' => 3,
                'currentTime' => '2025-01-02 00:00:00',
                'expected' => false
            ],
        ];
    }
}
