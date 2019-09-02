<?php

namespace App\Consumer;

use FOS\UserBundle\Model\UserManagerInterface;
use Psr\Log\LoggerInterface;
use Twig\Environment;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sends out Notifications over email
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class EmailNotificationConsumer extends AbstractNotificationConsumer
{
    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var Environment
     */
    protected $twigEnvironment;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * EmailNotificationConsumer constructor
     *
     * @param LoggerInterface      $logger
     * @param UserManagerInterface $userManager
     * @param Environment          $twigEnvironment
     * @param \Swift_Mailer        $mailer
     * @param ContainerInterface   $container
     */
    public function __construct(
        LoggerInterface $logger,
        UserManagerInterface $userManager,
        Environment $twigEnvironment,
        ContainerInterface $container,
        \Swift_Mailer $mailer
    ) {
        parent::__construct($logger);
        $this->userManager = $userManager;
        $this->twigEnvironment = $twigEnvironment;
        $this->container = $container;
        $this->mailer = $mailer;
    }

    protected function shouldHandle()
    {
        $emailSubscription = $this->notification->getRecipient()->isSubscribedToEmailNotifications();

        if (!$emailSubscription) {
            $this->logger->info(
                $this->notification->getRecipient()->getUsername()
                .' has unsubscribed to email notifications'
            );

            return false;
        }
        $this->logger->info(
            $this->notification->getRecipient()->getUsername()
            .' has subscribed to email notifications, sending an email'
        );

        return true;
    }

    /**
     * Handles an email Notification
     *
     * @return bool
     *
     * @throws \Exception
     */
    public function handle(): bool
    {
        $userEmail = $this->notification->getRecipient()->getEmail();

        // Get necessary parameters from the container
        $settingsUrl = $this->container->getParameter('app.librecores_url')."/user/settings/notifications";
        $logoUrl = $this->container->getParameter('app.librecores_url')."/static/img/logo_email_notification.png";
        $notificationFromAddress = $this->container->getParameter('app.notification_from_address');
        $notificationFromName = $this->container->getParameter('app.notification_from_name');

        $message = (new \Swift_Message($this->notification->getSubject()))
            // Set sender's email from parameters.yml
            ->setFrom([
                $notificationFromAddress => $notificationFromName,
            ])
            ->setTo($userEmail)
            ->setBody($this->twigEnvironment->render('template/email_notification_template.html.twig', [
                'username' => $this->notification->getRecipient()->getUsername(),
                'content' => $this->notification->getMessage(),
                'notificationSettingsUrl' => $settingsUrl,
                'logoUrl' => $logoUrl,
            ]), 'text/html')
            // Alternate body for users who have text/plain preferences
            ->addPart($this->twigEnvironment->render('template/email_notification_template.txt.twig', [
                'username' => $this->notification->getRecipient()->getUsername(),
                'content' => $this->notification->getMessage(),
                'notificationSettingsUrl' => $settingsUrl,
            ]), 'text/plain');

        $this->mailer->send($message);

        return true;
    }
}

?>
