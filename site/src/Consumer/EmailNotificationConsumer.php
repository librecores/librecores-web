<?php

namespace App\Consumer;

use FOS\UserBundle\Model\UserManagerInterface;
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
     * @var AppNotification $notification
     */
    protected $notification;

    /**
     * @var UserManagerInterface $userManager
     */
    protected $userManager;

    /**
     * @var \Swift_Mailer $mailer
     */
    protected $mailer;

    /**
     * EmailNotificationConsumer constructor.
     * @param UserManagerInterface $userManager
     */
    public function __construct(
        UserManagerInterface $userManager,
        AppNotification $notification,
        \Swift_Mailer $mailer
    ) {
        $this->notification = $notification;
        $this->userManager = $userManager;
        $this->mailer = $mailer;
    }

    /**
     * @param AMQPMessage $msg
     *
     * @return mixed|void
     *
     * @throws \Exception
     */
    public function execute(AMQPMessage $msg)
    {
        $this->notification = (unserialize($msg->body));
        try {
            if ($this->shouldBeHandledAsEmailNotification($this->notification)) {
                $this->sendEmail($this->notification, $this->mailer);
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param AppNotification $notification
     *
     * @return bool
     */
    public function shouldBeHandledAsEmailNotification(AppNotification $notification)
    {
        echo "Email Notification Consumer \n";
        $user = $this->userManager->findUserBy(array('id' => $this->notification->getUserIdentifier()));
        if ($notification->getType() === 'email_notification' && $user->isSubscribedToEmailNotifs()) {
            echo "Subscribed!";
            return true;
        }
        else return false;
    }

    /**
     * This method sends out an email to the User associated
     * with an Email Notification
     *
     * @param AppNotification $notification
     * @param \Swift_Mailer   $mailer
     */
    public function sendEmail(AppNotification $notification, \Swift_Mailer $mailer)
    {
        //$user = $this->userManager->findUserBy(array('id' => $this->notification->getUserIdentifier()));
        $message = (new \Swift_Message('Email Notification'))
            ->setFrom('aquibbaig97@gmail.com')
            ->setTo('aquibbaig97@gmail.com')
            ->setSubject('New Notification from Librecores')
            ->setBody($notification->getMessage(), 'text/plain');
        $mailer->send($message);
    }
}

?>
