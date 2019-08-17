<?php

namespace App\Tests\RepoCrawler;

use App\Consumer\WebNotificationConsumer;
use App\Entity\User;
use App\Util\Notification;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for the Web Notification Consumer
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class WebNotificationConsumerTest extends TestCase
{
    protected $notification;

    /**
     * Called before every test
     */
    public function setUp()
    {
        $this->notification = new Notification();
        $this->notification->setMessage('Your project was added to LibreCores')
            ->setSubject('New Project')
            ->setType('new_project');
        $user = new User();
        $user->setUsername('test');
        $this->notification->setRecipient($user);

        // Assert if fields are set properly
        $this->assertEquals('Your project was added to LibreCores', $this->notification->getMessage());
        $this->assertEquals('New Project', $this->notification->getSubject());
        $this->assertEquals('new_project', $this->notification->getType());
        $this->assertEquals('test', $this->notification->getRecipient()->getUsername());
    }

    /**
     * Called after every test
     */
    public function tearDown()
    {
        $this->notification = null;
    }

    /**
     * @test
     *
     * Test if notification of a specific type are handled
     */
    public function testIfMentionedTypesOfNotificationsAreHandled()
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockNotificationManager = $this->getMockBuilder(NotificationManager::class)
            ->setMethods(['addNotification'])
            ->disableOriginalConstructor()
            ->getMock();

        $webNotificationConsumer = new WebNotificationConsumer($mockLogger, $mockNotificationManager);
        $webNotificationConsumer->setNotification($this->notification);

        $msg = new AMQPMessage();
        $msg->body = serialize($this->notification);

        $mockWebNotificationConsumer = $this->getMockBuilder(WebNotificationConsumer::class)
            ->setMethods([ 'handle'])
            ->setConstructorArgs([$mockLogger, $mockNotificationManager])
            ->getMock();

        // If handle method is called
        // shouldHandle returns true
        $mockWebNotificationConsumer->expects($this->once())
            ->method('handle')
            ->willReturn(true);


        $mockWebNotificationConsumer->execute($msg);
    }

    /**
     * @test
     *
     * Test if unwanted types are not send to UI
     * but sent only to email
     */
    public function testIfOtherTypesOfNotificationsAreNotHandled()
    {
        $this->notification = new Notification();
        $this->notification->setMessage('Your project was added to LibreCores')
            ->setSubject('New Project')
            ->setType('undefined_type');
        $user = new User();
        $user->setUsername('test');
        $this->notification->setRecipient($user);

        // Assert if fields are set properly
        $this->assertEquals('Your project was added to LibreCores', $this->notification->getMessage());
        $this->assertEquals('New Project', $this->notification->getSubject());
        $this->assertEquals('undefined_type', $this->notification->getType());
        $this->assertEquals('test', $this->notification->getRecipient()->getUsername());

        $msg = new AMQPMessage();
        $msg->body = serialize($this->notification);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockNotificationManager = $this->getMockBuilder(NotificationManager::class)
            ->setMethods(['addNotification'])
            ->disableOriginalConstructor()
            ->getMock();

        $webNotificationConsumer = new WebNotificationConsumer($mockLogger, $mockNotificationManager);
        $webNotificationConsumer->setNotification($this->notification);

        $msg = new AMQPMessage();
        $msg->body = serialize($this->notification);

        $mockWebNotificationConsumer = $this->getMockBuilder(WebNotificationConsumer::class)
            ->setMethods([ 'handle'])
            ->setConstructorArgs([$mockLogger, $mockNotificationManager])
            ->getMock();

        // If handle method is not called
        // shouldHandle returns false
        $mockWebNotificationConsumer->expects($this->exactly(0))
            ->method('handle');

        $mockWebNotificationConsumer->execute($msg);
    }

    /**
     * @test
     *
     * Tests if a notification is added to database
     */
    public function testIfNotificationIsAdded()
    {
        $msg = new AMQPMessage();
        $msg->body = serialize($this->notification);

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockNotificationManager = $this->getMockBuilder(NotificationManager::class)
            ->setMethods(['addNotification'])
            ->disableOriginalConstructor()
            ->getMock();

        // If addNotification method is called,
        // notifications are in fact persisted
        $mockNotificationManager->expects($this->once())
            ->method('addNotification');

        $webNotificationConsumer = new WebNotificationConsumer($mockLogger, $mockNotificationManager);
        $webNotificationConsumer->execute($msg);
    }

    /**
     * @test
     *
     * Handle method always returns true to
     * prevent notifications from requeue in RabbitMQ
     */
    public function testIfNotificationsDontRequeue()
    {
        $mockWebNotificationConsumer = $this->getMockBuilder(WebNotificationConsumer::class)
            ->disableOriginalConstructor()
            ->setMethods([ 'execute'])
            ->getMock();

        $msg = new AMQPMessage();
        $msg->body = serialize($this->notification);

        // If execute method returns true,
        // notifications never requeue
        $mockWebNotificationConsumer->method('execute')
            ->with($msg)
            ->willReturn(true);
        $this->assertSame(true, $mockWebNotificationConsumer->execute($msg));
    }
}

?>
