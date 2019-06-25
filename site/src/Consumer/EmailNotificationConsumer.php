<?php

namespace App\Consumer;

use App\Util\Notification;
use Psr\Log\LoggerInterface;

/**
 * Sends out Notifications over email
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class EmailNotificationConsumer extends AbstractNotificationConsumer
{
    /**
     * EmailNotificationConsumer constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        parent::__construct($logger);
    }

    /**
     * Handles an email Notification
     *
     * @return bool
     */
    protected function handle(): bool
    {
        // TODO: Process the Notification($this->notification)
        echo "Email Notification Consumer";

        return true;
    }
}

?>
