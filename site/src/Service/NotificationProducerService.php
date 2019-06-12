<?php

namespace App\Service;

use OldSound\RabbitMqBundle\RabbitMq\ProducerInterface;
use App\Entity\AppNotification;

/**
 * Class NotificationProducer
 */
class NotificationProducerService
{
    /**
     * @var ProducerInterface
     */
    private  $notificationProducer;

    /**
     * NotificationService constructor.
     * @param ProducerInterface $notificationProducer
     */
    public function __construct(ProducerInterface $notificationProducer)
    {
        $this->notificationProducer = $notificationProducer;
    }

    /**
     * @param AppNotification $notification
     */
    public function publishNotification(AppNotification $notification)
    {
        $this->notificationProducer->publish((serialize($notification)));
    }
}

?>
