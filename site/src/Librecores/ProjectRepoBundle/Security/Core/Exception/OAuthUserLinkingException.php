<?php
namespace Librecores\ProjectRepoBundle\Security\Core\Exception;

use Symfony\Component\Security\Core\Exception\AuthenticationException;

class OAuthUserExistsException extends AuthenticationException
{
    private $username;
    private $email;

    /**
     * {@inheritdoc}
     */
    public function getMessageKey()
    {
        return 'A user with your username or email address already '.
               'exists on LibreCores. If this is you, please log in and '.
               'connect your account. Otherwise, please create a new '.
               'account on LibreCores and connect it then.';
    }

    /**
     * Get the username.
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set the username.
     *
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * Get the email address
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the email address.
     *
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array(
            $this->username,
            $this->email,
            parent::serialize(),
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list($this->username, $this->email, $parentData) = unserialize($str);

        parent::unserialize($parentData);
    }

    /**
     * {@inheritdoc}
     */
    public function getMessageData()
    {
        return array(
            '{{ username }}' => $this->username,
            '{{ email }}' => $this->email
        );
    }
}
