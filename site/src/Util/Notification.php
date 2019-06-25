<?php

namespace App\Util;

use App\Entity\User;
use DateTime;

/**
 * Data class for all Notifications
 *
 * @author Aquib Baig <aquibbaig97@gmail.com>
 */
class Notification
{
    /**
     * Subject of the Notification
     *
     * @var string $subject
     */
    protected $subject;

    /**
     * Notification message
     *
     * @var string $message
     */
    protected $message;

    /**
     * Recipient of the Notification
     *
     * @var User $recipient
     */
    protected $recipient;

    /**
     * The type of Notification
     *
     * @var string $type
     */
    protected $type;

    /**
     * When is the Notification created
     *
     * @var DateTime $createdAt
     */
    protected $createdAt;

    /**
     * Notification constructor.
     */
    public function __construct()
    {
        $this->setCreatedAt(new DateTime());
    }

    /**
     * Gets the Notification Subject
     *
     * @return string
     */
    public function getSubject(): string
    {
        return $this->subject;
    }

    /**
     * Sets the Notification Subject
     *
     * @param string $subject
     *
     * @return $this
     */
    public function setSubject(string $subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get the Notification Message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * Set the Notification Message
     *
     * @param string $message
     *
     * @return $this
     */
    public function setMessage(string $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Returns the type of the Notification
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Set the Notification type
     *
     * @param string $type
     *
     * @return $this
     */
    public function setType(string $type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the recipient of this notification
     *
     * @return User
     */
    public function getRecipient(): User
    {
        return $this->recipient;
    }

    /**
     * Set the recipient of this message
     *
     * @param User $recipient
     *
     * @return $this
     */
    public function setRecipient(User $recipient)
    {
        $this->recipient = $recipient;

        return $this;
    }

    /**
     * Get the creation date of this notification
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Set the creation date of this notification
     *
     * @param DateTime $createdAt
     *
     * @return $this
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
