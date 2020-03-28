<?php

declare(strict_types=1);

namespace Notifier\Tests\Message\Sender;

use PHPUnit\Framework\TestCase;
use Notifier\Channel\Email\SimpleEmailNotificationSender;
use Notifier\Channel\Email\EmailMessage;
use Notifier\Message\Actor\Actor;
use Notifier\Channel\Options;
use Notifier\Tests\TestAsset\Message\DummyMessage;
use Notifier\Channel\Exception\UnsupportedMessage;
use Notifier\Channel\Exception\SendingNotificationFailed;

class SimpleEmailNotificationSenderTest extends TestCase
{
    /**
     * @var array
     */
    private $sentParameters;

    protected function setUp()
    {
        parent::setUp();

        $this->sentParameters = [];
    }

    public function mailer()
    {
        $this->sentParameters[] = func_get_args();

        return true;
    }

    public function mailerError()
    {
        return false;
    }

    private function getMailer($maxColumnWidth = 70)
    {
        return new SimpleEmailNotificationSender($maxColumnWidth, [$this, 'mailer']);
    }

    public function testSendWithDefaultOptions()
    {
        $message = new EmailMessage(
            [
                new Actor('test1@example.com', 'Test1'),
                new Actor('test2@example.com', 'Test2'),
            ],
            'Test',
            'test test test'
        );

        $this->getMailer()->send($message);

        $this->assertNotEmpty($this->sentParameters);
        $params = $this->sentParameters[0];
        $this->assertCount(5, $params);
        $this->assertEquals('Test1 <test1@example.com>,Test2 <test2@example.com>', $params[0]);
        $this->assertEquals('Test', $params[1]);
        $this->assertEquals('test test test', $params[2]);
        $this->assertEquals("Content-type: text/plain; charset=utf-8\r\n", $params[3]);
        $this->assertSame('', $params[4]);
    }

    public function testExceptionIsRaisedInCaseOfUnsupportedMessageType()
    {
        $this->expectException(UnsupportedMessage::class);

        $message = new DummyMessage(
            [
                new Actor('test1@example.com')
            ],
            'test test test'
        );

        $this->getMailer()->send($message);
    }

    public function testEmailContentWordWrap()
    {
        $message = new EmailMessage(
            [
                new Actor('test1@example.com'),
                new Actor('test2@example.com'),
            ],
            'Test',
            'test test test'
        );

        $this->getMailer(4)->send($message);

        $this->assertNotEmpty($this->sentParameters);
        $params = $this->sentParameters[0];
        $this->assertCount(5, $params);
        $this->assertEquals("test\ntest\ntest", $params[2]);
    }

    public function testCustomEmailContentType()
    {
        $message = new EmailMessage(
            [
                new Actor('test1@example.com'),
                new Actor('test2@example.com'),
            ],
            'Test',
            'test test test',
            null,
            new Options([
                'content_type' => 'text/html',
            ])
        );

        $this->getMailer()->send($message);

        $this->assertNotEmpty($this->sentParameters);
        $params = $this->sentParameters[0];
        $this->assertCount(5, $params);
        $this->assertEquals("Content-type: text/html; charset=utf-8\r\nMIME-Version: 1.0\r\n", $params[3]);
    }

    public function testEmailSenderHeaders()
    {
        $message = new EmailMessage(
            [
                new Actor('test1@example.com'),
            ],
            'Test',
            'test test test',
            new Actor('john.doe@example.com')
        );

        $this->getMailer()->send($message);

        $this->assertNotEmpty($this->sentParameters);
        $params = $this->sentParameters[0];
        $this->assertCount(5, $params);
        $this->assertContains("From: john.doe@example.com\r\nReply-To: john.doe@example.com", $params[3]);
    }

    public function testExceptionIsRaisedIfEmailNotDelivered()
    {
        $this->expectException(SendingNotificationFailed::class);

        $message = new EmailMessage(
            [
                new Actor('test1@example.com'),
                new Actor('test2@example.com'),
            ],
            'Test',
            'test test test'
        );

        $messageSender = new SimpleEmailNotificationSender(70, [$this, 'mailerError']);
        $messageSender->send($message);
    }
}
