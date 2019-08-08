<?php

namespace App\Command;

use App\Util\Notification;
use App\Service\NotificationCreatorService;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Create a notification for a given user
 */
class CreateNotificationCommand extends Command
{
    /**
     * @var string
     */
    protected static $defaultName = 'librecores:send-notification';

    /**
     * @var NotificationCreatorService
     */
    private $notificationCreator;

    /**
     * @var UserManagerInterface
     */
    private $userManager;

    /**
     * CreateNotificationCommand constructor.
     *
     * @param NotificationCreatorService $notificationCreator
     * @param UserManagerInterface       $userManager
     */
    public function __construct(NotificationCreatorService $notificationCreator, UserManagerInterface $userManager)
    {
        parent::__construct();
        $this->notificationCreator = $notificationCreator;
        $this->userManager = $userManager;
    }

    /**
     * Configuration for the Command
     */
    protected function configure()
    {
        $this->setDescription('Send a notification to a user')
            ->setHelp('This command allows you to send a notification to a user');
        $this->addArgument('subject', InputArgument::REQUIRED, 'Add notification subject');
        $this->addArgument('message', InputArgument::REQUIRED, 'Add notification message');
        $this->addArgument('type', InputArgument::REQUIRED, 'Specify notification type');
        $this->addArgument('username', InputArgument::REQUIRED, 'Specify user to whom this notification will be sent to');
    }

    /**
     * Send notification to the user
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $user = $this->userManager->findUserByUsername($input->getArgument('username'));

        $notification = new Notification();
        $notification->setSubject($input->getArgument('subject'));
        $notification->setMessage($input->getArgument('message'));
        $notification->setType($input->getArgument('type'));
        $notification->setRecipient($user);

        $this->notificationCreator->createNotification($notification);
    }
}

?>
