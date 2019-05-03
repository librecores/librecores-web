<?php


namespace App\Form\DataTransformer;

use FOS\UserBundle\Model\UserInterface;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class UserToEmailTransformer implements DataTransformerInterface
{
    /** @var UserManagerInterface */
    private $userManager;

    public function __construct(UserManagerInterface $userManager)
    {
        $this->userManager = $userManager;
    }

    /**
     * Transforms a User entity into a string.
     *
     * Hardcoded to return the user's email
     *
     * @param UserInterface $value
     *
     * @return string
     */
    public function transform($value)
    {
        if (!$value) {
            return '';
        }

        return $value->getEmail();
    }

    /**
     * Transforms an email entity into a user entity.
     *
     * @param string $value
     *
     * @return UserInterface
     */
    public function reverseTransform($value)
    {
        if (!$value) {
            return null;
        }

        $user = $this->userManager->findUserByEmail($value);

        if (!$user) {
            throw new TransformationFailedException("User not found");
        }

        return $user;
    }
}
