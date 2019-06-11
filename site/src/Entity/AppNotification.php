<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mgilet\NotificationBundle\Entity\NotificationInterface;
use Mgilet\NotificationBundle\Model\Notification as NotificationModel;

/**
 * Class AppNotification
 * @ORM\Entity
 *
 * @package Acme\Entity
 */
class AppNotification extends NotificationModel implements NotificationInterface
{
    /**
     * @ORM\Column(name="notification_type", type="string", length=255, nullable=false)
     */
    protected  $type;

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
