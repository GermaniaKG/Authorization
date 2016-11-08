<?php
namespace Germania\Authorization;

use Psr\Log\LoggerInterface;
use Psr\Http\Message\ServerRequestInterface as Request;


/**
 * This extension of AuthorizationMiddleware uses
 * Slim 3 Framework's Route names for determining authorization.
 *
 * See Slim 3 docs: http://www.slimframework.com/docs/objects/router.html#route-names
 */
class RouteNameAuthorizationMiddleware extends AuthorizationMiddleware
{


    /**
     * @param Callable               $authorizer   Callable that accepts a route name and returns bool.
     * @param LoggerInterface|null   $logger       Optional: PSR-3 Logger
     */
    public function __construct( Callable $authorizer, LoggerInterface $logger = null )
    {
        parent::__construct($authorizer, [$this, 'getRouteName'], $logger);
    }


    /**
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     *
     * @return string
     */
    public function getRouteName( Request $request) {

        if (!$route = $request->getAttribute('route')):
            $this->logger->info("Request attribute 'route' not available");
            return null;
        endif;

        if (!$route_name = $route->getName()):
            $this->logger->info("Route name not available");
            return null;
        endif;

        return $route_name;
    }
}
