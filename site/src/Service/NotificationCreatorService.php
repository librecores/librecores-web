<?php

namespace App\Service;

use App\Util\Notification;
use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;

/**
 * Creates notification for a given user
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class NotificationCreatorService
{
    /**
     * @var ProducerInterface
     */
    private $notificationProducer;

    /**
     * NotificationCreatorService constructor.
     * @param ProducerInterface $notificationProducer
     */
    public function __construct(ProducerInterface $notificationProducer)
    {
        $this->notificationProducer = $notificationProducer;
    }

    /**
     * Publishes a notification to RabbitMQ
     *
     * @param Notification $notification
     */
    public function createNotification(Notification $notification)
    {
        $this->notificationProducer->publish(serialize($notification));
    }
}

?>
