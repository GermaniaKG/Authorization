<?php
namespace Germania\Authorization;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;


/**
 * This extension of AuthorizationMiddleware uses
 * PSR7 Request's getUri method for determining authorization.
 */
class RequestUriAuthorizationMiddleware extends AuthorizationMiddleware
{

    /**
     * @param Callable               $authorizer   Callable that accepts a route name and returns bool.
     * @param LoggerInterface|null   $logger       Optional: PSR-3 Logger
     */
    public function __construct( Callable $authorizer, LoggerInterface $logger = null )
    {

        parent::__construct($authorizer, [$this, 'getUri'], $logger);
    }

    public function getUri( Request $request) {
        return (string) $request->getUri();
    }

}
