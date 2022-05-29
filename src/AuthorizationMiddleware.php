<?php
namespace Germania\Authorization;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;


/**
 * This Middleware sets Response's Status Code to "401 Unauthorized"
 * if the passed authorizer callable returns FALSE.
 */
class AuthorizationMiddleware implements MiddlewareInterface
{
    /**
     * @var Callable
     */
    public $authorizer;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * HTTP Status Code for "Unauthorized". Usually 401.
     * @var string
     */
    public $unauthorized_status_code = 401;

    /**
     * @var Callable
     */
    public $get_url;


    /**
     * @param Callable               $authorizer   Callable that accepts a route name and returns bool.
     * @param Callable               $get_url      Callable that accepts a PSR7 Request
     *                                             and returns the URL to check against authorizer.
     * @param LoggerInterface|null   $logger       Optional: PSR-3 Logger
     */
    public function __construct( Callable $authorizer, Callable $get_url, LoggerInterface $logger = null )
    {
        $this->authorizer   = $authorizer;
        $this->get_url      = $get_url;
        $this->logger       = $logger ?: new NullLogger;
    }




    public function process(Request $request, RequestHandlerInterface $handler) : Response
    {
        $get_url = $this->get_url;
        if (!$url = $get_url( $request )):
            $this->logger->notice("Route name not available, work with empty value?!");
        endif;


        // ---------------------------------------
        // 1. Authorization:
        //    If authorizer() is TRUE, user is allowed.
        // ---------------------------------------
        $authorize  = $this->authorizer;
        $authorized = $authorize($url);


        // ---------------------------------------
        // 2. If Authentication required,
        //    set "401 Unauthorized" Header to response
        // ---------------------------------------
        if (!$authorized):
            $this->logger->info("Before Route: Not authorized; set Status Code ", [
                'url'  => $url,
                'status' => $this->unauthorized_status_code
            ]);

            // Set "401 Unauthorized" Header to response
            $response = (new \Nyholm\Psr7\Factory\Psr17Factory)->createResponse($this->unauthorized_status_code);
            return $response;
        else:
            $this->logger->info("Before Route: Authorization successful", [
                'url'  => $url
            ]);
            // Do nothing else here
        endif;


        // ---------------------------------------
        // 3. Call next middleware
        // ---------------------------------------
        $response = $handler->handle($request);

        $this->logger->debug("After Route: noop");

        // ---------------------------------------
        // 4. Finish
        // ---------------------------------------
        return $response;
    }




    /**
     * @param  \Psr\Http\Message\ServerRequestInterface $request  PSR7 request
     * @param  \Psr\Http\Message\ResponseInterface      $response PSR7 response
     * @param  callable                                 $next     Next middleware
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function __invoke(Request $request, Response $response, $next)
    {
        $get_url = $this->get_url;
        if (!$url = $get_url( $request )):
            $this->logger->notice("Route name not available, work with empty value?!");
        endif;


        // ---------------------------------------
        // 1. Authorization:
        //    If authorizer() is TRUE, user is allowed.
        // ---------------------------------------
        $authorize  = $this->authorizer;
        $authorized = $authorize($url);


        // ---------------------------------------
        // 2. If Authentication required,
        //    set "401 Unauthorized" Header to response
        // ---------------------------------------
        if (!$authorized):
            $this->logger->info("Before Route: Not authorized; set Status Code ", [
                'url'  => $url,
                'status' => $this->unauthorized_status_code
            ]);

            // Set "401 Unauthorized" Header to response
            $response = $response->withStatus( $this->unauthorized_status_code );
        else:
            $this->logger->info("Before Route: Authorization successful", [
                'url'  => $url
            ]);
            // Do nothing else here
        endif;


        // ---------------------------------------
        // 3. Call next middleware
        // ---------------------------------------
        $response = $next($request, $response);

        $this->logger->debug("After Route: noop");

        // ---------------------------------------
        // 4. Finish
        // ---------------------------------------
        return $response;
    }

}
