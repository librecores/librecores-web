<?php

namespace App\Controller;

use App\Entity\AppNotification;
use Mgilet\NotificationBundle\Annotation\Notifiable;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Mgilet\NotificationBundle\Manager\NotificationManager;

/**
 * Class NotificationController
 * @package App\Controller
 */
class NotificationController extends AbstractController
{
    /**
     * @var NotificationManager
     */
    protected $notificationManager;

    /**
     * NotificationController constructor.
     * @param NotificationManager $notificationManager
     */
    public function __construct(NotificationManager $notificationManager)
    {
        $this->notificationManager = $notificationManager;
    }

    /**
     * Set a Notification as seen
     *
     * @Route("/seen/{notifiable}/{notification}", name="notification_mark_seen")
     * @Method("POST")
     * @param int $notifiable id
     * @param int $notification id
     *
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\EntityNotFoundException
     * @throws \LogicException
     *
     */
    public function markAsSeenAction($notifiable, $notification)
    {
        $this->notificationManager->markAsSeen(
            $this->notificationManager->getNotifiableInterface($this->notificationManager->getNotifiableEntityById($notifiable)),
            $this->notificationManager->getNotification($notification),
            true
        );
    return $this->redirectToRoute('librecores_site_home');
    }

    /**
     * Remove a Notification
     *
     * @Route("/remove/{notifiable}/{notification}", name="remove_notification")
     * @Method("POST")
     * @param Notifiable      $notifiable
     * @param AppNotification $notification
     *
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function removeAction($notifiable, $notification)
    {
        $this->notificationManager->removeNotification(
            [$this->notificationManager->getNotifiableInterface($this->notificationManager->getNotifiableEntityById($notifiable))],
            $this->notificationManager->getNotification($notification),
        );

        $this->notificationManager->deleteNotification($this->notificationManager->getNotification($notification), true);

        return $this->redirectToRoute('librecores_site_home');
    }
}

?>
