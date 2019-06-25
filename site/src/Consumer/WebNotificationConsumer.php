<?php

namespace App\Consumer;

use App\Util\Notification;
use Psr\Log\LoggerInterface;

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
     * WebNotificationConsumer constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * Handles Notifications that will be sent to UI
     *
     * @return bool
     */
    protected function handle(): bool
    {
        // TODO: Process the Notification($this->notification)
        echo "Web Notification Consumer";

        return true;
    }
}

?>
