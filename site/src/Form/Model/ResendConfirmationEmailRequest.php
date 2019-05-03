<?php


namespace App\Form\Model;

use FOS\UserBundle\Model\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

class ResendConfirmationEmailRequest
{
    /**
     * @var UserInterface
     *
     * @Assert\NotNull
     */
    private $user;

    /**
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * @param UserInterface $user
     */
    public function setUser(UserInterface $user): void
    {
        $this->user = $user;
    }
}
