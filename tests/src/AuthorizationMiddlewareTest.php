<?php
namespace tests;

use Germania\Authorization\AuthorizationMiddleware;
use Psr\Log\LoggerInterface;

use Slim\Http\Environment;
use Slim\Http\Response;
use Slim\Http\Request;

/**
 * @coversDefaultClass \Germania\Authorization\Authorization
 */
class AuthorizationMiddlewareTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider provideData
     */
    public function testInstantiation( $auth_result, $url, $expected_status_code)
    {
        $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI' => '/foo/bar',
            'QUERY_STRING' => 'abc=123&foo=bar',
            'SERVER_NAME' => 'example.com',
            'CONTENT_TYPE' => 'application/json;charset=utf8',
            'CONTENT_LENGTH' => 15
        ]);

        // Build constructor arguments
        $authorizer = function( $url ) use ($auth_result) {
            return $auth_result;
        };

        $get_url = function( $request ) use ($url) {
            return $url;
        };

        // Build SUT and invoke
        $sut = new AuthorizationMiddleware($authorizer, $get_url);
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
            [ false, '/foo',  401],
            [ true,  '/foo',  200],
            [ true,  '',      200],
            [ false, '',      401]
        );
    }
}
