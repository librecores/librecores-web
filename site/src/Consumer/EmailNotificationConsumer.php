<?php

namespace App\Consumer;



use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use App\Entity\AppNotification;
use Psr\Log\LoggerInterface;

/**
 * Class EmailNotificationConsumer
 * TODO: Implementation of this class
 */
class EmailNotificationConsumer implements ConsumerInterface
{
    /**
     * @param AMQPMessage $msg
     *
     * @return mixed|void
     */
    public function execute(AMQPMessage $msg)
    {
        $notification = (unserialize($msg->body));
        if ($this->shouldBeHandledAsEmailNotification($notification)) {
            echo "Email";
        }
    }

    /**
     * @param AppNotification $notification
     *
     * @return bool
     */
    public function shouldBeHandledAsEmailNotification(AppNotification $notification)
    {
        if ($notification->getType() === 'email_notification') {
            return true;
        }
        else return false;
    }
}

?>
