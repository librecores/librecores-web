<?php

namespace App\Consumer;

use App\Util\Notification;
use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use Exception;

/**
 * Class AbstractNotificationConsumer
 *
 * Base class for all the Notification Consumers that we have
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
abstract class AbstractNotificationConsumer implements ConsumerInterface
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Notification $notification
     */
    protected $notification;

    /**
     * AbstractNotificationConsumer constructor
     *
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return bool
     *
     * @throws Exception
     */
    public function execute(AMQPMessage $msg)
    {
        $notification = unserialize($msg->body);
        $this->setNotification($notification);
        try {
            if ($this->shouldHandle()) {
                return $this->handle();
            }

            return true; //don't requeue
        } catch (Exception $e) {
            // Log out exceptions if they occur, and keep Consumers running for
            // next requests
            $this->logger->error(
                "Processing the Notification resulted in an ".get_class($e)
            );
            $this->logger->error("Message: ".$e->getMessage());
            $this->logger->error("Trace: ".$e->getTraceAsString());

            return false;
        }
    }

    public function setNotification(Notification $notification)
    {
        $this->notification = $notification;
    }

    /**
     * Should a Notification be handled by a the Consumer?
     *
     * @return bool
     */
    protected function shouldHandle()
    {
        return true;
    }

    /**
     * Actually processes a Notification to its sink
     *
     * @return mixed
     */
    abstract public function handle():bool;
}

?>
