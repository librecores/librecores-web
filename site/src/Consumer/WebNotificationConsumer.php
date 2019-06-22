<?php

namespace App\Consumer;

use App\Entity\AppNotification;
use Mgilet\NotificationBundle\Manager\NotificationManager;
use FOS\UserBundle\Model\UserManagerInterface;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Exception;

/**
 * Class WebNotificationConsumer
 */
class WebNotificationConsumer implements ConsumerInterface
{
    /**
     * @var AppNotification
     */
    protected $notification;

    /**
     * @var NotificationManager
     */
    protected $notificationManager;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var $logger
     */
    private $logger;

    /**
     * PushNotificationConsumer constructor.
     * @param AppNotification      $notification
     * @param NotificationManager  $notificationManager
     * @param UserManagerInterface $userManager
     */
    public function __construct(
        AppNotification $notification,
        NotificationManager $notificationManager,
        UserManagerInterface $userManager
    ) {
        $this->notification = $notification;
        $this->notificationManager = $notificationManager;
        $this->userManager = $userManager;
    }

    /**
     * Find the user that created the notification
     * and persist it in the database
     *
     * @param AMQPMessage $msg
     *
     * @throws \Exception
     */
    public function execute(AMQPMessage $msg)
    {
        echo "Web Notification Consumer\n";
        $this->notification = (unserialize($msg->body));
        try {
            if ($this->shouldBeHandledAsWebNotification($this->notification)) {
                $user = $this->userManager->findUserBy(array('id' => $this->notification->getUserIdentifier()));
                $this->notificationManager->addNotification(array($user), $this->notification, true);
            }
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    /**
     * This method handles whether a notification from the Queue
     * is to be handled as Web Notification, based on its type
     * and User Notification Settings
     *
     * @param AppNotification $notification
     *
     * @return bool
     */
    public function shouldBeHandledAsWebNotification(AppNotification $notification)
    {
        $user = $this->userManager->findUserBy(array('id' => $this->notification->getUserIdentifier()));
        if ($notification->getType() === 'web_notification' && $user->isSubscribedToWebNotifs()) {
            return true;
        } else {
            return false;
        }
    }
}

?>
