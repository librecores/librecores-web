<?php


namespace App\Service\GitHub;

use Github\Exception\ErrorException;

class AuthenticationRequiredException extends ErrorException
{
    public function __construct()
    {
        parent::__construct("Authentication required", 401);
    }
}
