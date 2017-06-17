<?php

declare(strict_types=1);

namespace Notify\Tests\Notification;

use Notify\Recipients;
use Notify\NotificationInterface;
use PHPUnit\Framework\TestCase;
use Notify\Tests\TestAsset\Notification\TestNotification;
use Notify\Tests\TestAsset\Entity\User;
use Notify\Message\EmailMessage;

class NotificationTest extends TestCase
{
    /**
     * @var NotificationInterface
     */
    protected $notification;

    protected function setUp()
    {
        parent::setUp();

        $this->notification = new TestNotification();
    }

    public function testGettingSupportedChannels()
    {
        $supportedChannels = $this->notification->getSupportedChannels();

        $this->assertEquals(['email'], $supportedChannels);
    }

    public function testCreatingMessage()
    {
        $emailMessages = $this->notification->getMessages(
            'email',
            new Recipients([
                new User([
                    'email' => 'test@example.com'
                ])
            ])
        );

        $emailMessage = current($emailMessages);

        $this->assertInstanceOf(EmailMessage::class, $emailMessage);
    }
}
