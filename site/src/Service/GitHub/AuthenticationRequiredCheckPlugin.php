<?php


namespace App\Service\GitHub;

use Http\Client\Common\Plugin;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AuthenticationRequiredCheckPlugin implements Plugin
{

    /**
     * @inheritDoc
     */
    public function handleRequest(RequestInterface $request, callable $next, callable $first)
    {
        return $next($request)->then(
            function (ResponseInterface $response) use ($request) {
                if ((401 === $response->getStatusCode()) &&
                    !$response->hasHeader('X-GitHub-OTP')) {
                    throw new AuthenticationRequiredException();
                }

                return $response;
            }
        );
    }
}
