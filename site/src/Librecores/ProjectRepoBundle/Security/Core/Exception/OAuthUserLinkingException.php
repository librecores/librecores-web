<?php

namespace Librecores\ProjectRepoBundle\Security\Core\Exception;

use HWI\Bundle\OAuthBundle\Security\Core\Exception\AccountNotLinkedException;

class OAuthUserLinkingException extends AccountNotLinkedException
{
    /**
     * @var string[]
     */
    protected $oAuthData;

    public function getOAuthData()
    {
        return $this->oAuthData;
    }

    public function setOAuthData($oAuthData)
    {
        $this->oAuthData = $oAuthData;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->oAuthData,
                parent::serialize(),
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($str)
    {
        list(
            $this->oAuthData,
            $parentData
            ) = unserialize($str);
        parent::unserialize($parentData);
    }
}
