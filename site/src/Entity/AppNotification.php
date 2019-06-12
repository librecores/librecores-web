<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mgilet\NotificationBundle\Entity\NotificationInterface;
use Mgilet\NotificationBundle\Model\Notification as NotificationModel;
use phpDocumentor\Reflection\Types\Integer;

/**
 * Class AppNotification
 * @ORM\Entity
 *
 * @package Acme\Entity
 */
class AppNotification extends NotificationModel implements NotificationInterface, \JsonSerializable
{
    /**
     * @ORM\Column(name="notification_type", type="string", length=255, nullable=false)
     */
    protected $type;

    /**
     * User Identifier to find user(s) in the Consumer Phase
     *
     * @ORM\Column(name="userIdentifier", type="integer", nullable=false)
     */
    protected $userIdentifier;

    /**
     * @return Integer
     */
    public function getUserIdentifier()
    {
        return $this->userIdentifier;
    }

    /**
     * @param integer $userIdentifier
     */
    public function setUserIdentifier($userIdentifier)
    {
        $this->userIdentifier = $userIdentifier;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type): string
    {
        $this->type = $type;
        return $this;
    }


}

?>
