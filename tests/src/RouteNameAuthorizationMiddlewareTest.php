<?php
namespace tests;

use Germania\Authorization\RouteNameAuthorizationMiddleware;
use Psr\Log\LoggerInterface;

use Slim\Http\Environment;
use Slim\Http\Response;
use Slim\Http\Request;
use Slim\Route;

/**
 * @coversDefaultClass \Germania\Authorization\Authorization
 */
class RouteNameAuthorizationMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testInstantiation( $auth_result, $expected_status_code, $use_route_mock, $mock_route_name)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => '/foo/bar',
            'QUERY_STRING'   => 'abc=123&foo=bar',
            'SERVER_NAME'    => 'example.com',
            'CONTENT_TYPE'   => 'application/json;charset=utf8',
            'CONTENT_LENGTH' => 15
        ]);


        // Build constructor arguments
        $authorizer = function( $url ) use ($auth_result) {
            return $auth_result;
        };


        // Build route object mock
        $route_mock = null;
        if ($use_route_mock):
            $route = $this->prophesize( Route::class );
            $route->getName()->shouldBeCalled()->willReturn( $mock_route_name );
            $route_mock = $route->reveal();
        endif;

        // Build request object
        $request = Request::createFromEnvironment( $env );
        $request = $request->withAttribute('route', $route_mock);


        // Build SUT and invoke
        $sut = new RouteNameAuthorizationMiddleware($authorizer);
        $response = $sut($request, new Response, function( $request, $response ) {
            return $response;
        });


        // Evaluate
        $this->assertEquals( $response->getStatusCode(), $expected_status_code );
        return $sut;
    }


    public function provideData()
    {


        return array(
            [ false, 401, "use_route_mock", "value"],
            [ true,  200, "use_route_mock", "value"],
            [ false, 401, "use_route_mock", null],
            [ true,  200, "use_route_mock", null],
            [ true,  200, false, null],
            [ false, 401, false, null]
        );
    }
}
