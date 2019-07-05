<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mgilet\NotificationBundle\Entity\NotificationInterface;
use Mgilet\NotificationBundle\Model\Notification as NotificationModel;

/**
 * A Notification to be sent
 *
 * Overrides Mgilet\NotificationBundle\Entity\Notification
 *
 * @ORM\Table("Notification")
 * @ORM\Entity
 */
class Notification extends NotificationModel implements NotificationInterface
{
    /**
     * The type of the Notification
     *
     * @var String
     *
     * @ORM\Column(name="type", nullable=false)
     */
    protected $type;

    /**
     * Get the Notification type
     *
     * @return String
     */
    public function getType(): String
    {
        return $this->type;
    }

    /**
     * Set the Notification type
     *
     * @param String $type
     *
     * @return $this
     */
    public function setType(String $type)
    {
        $this->type = $type;

        return $this;
    }
}
