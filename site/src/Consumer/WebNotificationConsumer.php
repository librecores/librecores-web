<?php

namespace App\Consumer;

use App\Entity\Notification;
use Doctrine\ORM\OptimisticLockException;
use Psr\Log\LoggerInterface;
use Mgilet\NotificationBundle\Manager\NotificationManager;

/**
 * Class WebNotificationConsumer
 *
 * This class persists Notifications to the database
 * and sends them out to the UI
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class WebNotificationConsumer extends AbstractNotificationConsumer
{

    /**
     * @var NotificationManager
     */
    protected $notificationManager;

    /**
     * WebNotificationConsumer constructor
     *
     * @param LoggerInterface     $logger
     * @param NotificationManager $notificationManager
     */
    public function __construct(
        LoggerInterface $logger,
        NotificationManager $notificationManager
    ) {
        parent::__construct($logger);
        $this->notificationManager = $notificationManager;
    }

    /**
     * Should a notification be handled by this Consumer?
     *
     * @return bool
     */
    protected function shouldHandle(): bool
    {
        $type = $this->notification->getType();
        switch ($type) {
            case "new_project":
                $this->logger->info('Processing the Notification...');

                return true;
            default:
                $this->logger->info('Something went wrong. Check the Notification type');

                return false;
        }
    }

    /**
     * Handles Notifications that will be sent to UI
     *
     * @return bool
     *
     * @throws OptimisticLockException
     */
    public function handle(): bool
    {
        echo "\nWeb Notification Consumer\n";

        $notification = new Notification();

        // Populate the Notification Entity
        $notification->setMessage($this->notification->getMessage());
        $notification->setType($this->notification->getType());
        $notification->setSubject($this->notification->getSubject());
        $notification->setDate($this->notification->getCreatedAt());

        // Persist the Notification
        $this->notificationManager->addNotification(
            [$this->notification->getRecipient()],
            $notification,
            true
        );

        return true;
    }
}

?>
