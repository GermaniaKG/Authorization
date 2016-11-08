<?php
namespace tests;

use Germania\Authorization\RequestUriAuthorizationMiddleware;
use Psr\Log\LoggerInterface;

use Slim\Http\Environment;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 * @coversDefaultClass \Germania\Authorization\Authorization
 */
class RequestUriAuthorizationMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testInstantiation( $auth_result, $expected_status_code)
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

        // Build SUT and invoke
        $sut = new RequestUriAuthorizationMiddleware($authorizer);
        $response = $sut(Request::createFromEnvironment( $env ), new Response, function( $request, $response ) {
            return $response;
        });

        // Evaluate
        $this->assertEquals( $response->getStatusCode(), $expected_status_code );
        return $sut;
    }


    public function provideData()
    {
        return array(
            [ false, 401],
            [ true,  200],
            [ true,  200],
            [ false, 401]
        );
    }
}
