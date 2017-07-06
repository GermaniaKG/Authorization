<?php
namespace tests;

use Germania\Authorization\Authorization;
use Germania\Authorization\TaskNotFoundException;
use Interop\Container\Exception\NotFoundException;
use Interop\Container\ContainerInterface as InteropContainerInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Germania\Authorization\Authorization
 */
class AuthorizationTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiation()
    {
        $default_permission = true;

        $sut = new Authorization(array(
            '/foo' => [ "admin",     "superuser"],
            '/bar' => [ "superuser", "registered"]
        ), $default_permission);

        $this->assertInstanceOf( ContainerInterface::class, $sut );
        $this->assertInstanceOf( InteropContainerInterface::class, $sut );
        $this->assertInstanceOf( LoggerInterface::class, $sut->log );
        $this->assertInternalType( "array", $sut->acl );

        return $sut;
    }


    /**
     * @depends testInstantiation
     */
    public function testInteropContainerInterface( $sut )
    {
        $this->assertTrue( $sut->has("/foo") );
        $this->assertFalse( $sut->has("something-else") );

        $this->assertNotEmpty( $sut->get("/foo"));

        $this->expectException(TaskNotFoundException::class);
        $this->assertFalse( $sut->get("something-else") );
    }




    /**
     * @depends testInstantiation
     */
    public function testInvokation( $sut )
    {
        $p1 = $sut->__invoke("/foo", array("superuser"));
        $this->assertTrue( $p1 );

        $p2 = $sut->__invoke("/bar", array("anyother_usergroup"));
        $this->assertFalse( $p2 );
    }


    /**
     * @depends testInstantiation
     */
    public function testAuthorizeMethod( $sut )
    {
        $roles = [ 'admin', 'somegroup' ];

        $p1 = $sut->authorize("/foo", $roles);
        $this->assertTrue( $p1 );

        $p2 = $sut->authorize("/bar", $roles);
        $this->assertFalse( $p2 );
    }





    /**
     * @dataProvider provideNumericAcl
     */
    public function testNumericPermissions( $acl, $roles, $default_permission)
    {

        $sut = new Authorization($acl, $default_permission);
        $p1 = $sut->authorize("/notdefined", $roles);

        $this->assertEquals( $p1, $default_permission );
    }



    /**
     * @dataProvider provideDefaultValuesWithDefaultPermission
     */
    public function testDefaultPermissions( $acl, $roles, $default_permission)
    {

        $sut = new Authorization($acl, $default_permission);
        $p1 = $sut->authorize("/notdefined", $roles);

        $this->assertEquals( $p1, $default_permission );
    }





    public function provideNumericAcl()
    {
        $acl = array(
            '/foo' => [ 3, 2],
            '/bar' => [ 2, 1]
        );
        $roles = [ 3, 4 ];

        return array(
            [ $acl, $roles, true ],
            [ $acl, $roles, false ]
        );
    }

    public function provideDefaultValuesWithDefaultPermission()
    {
        $acl = array(
            '/foo' => [ "admin",     "superuser"],
            '/bar' => [ "superuser", "registered"]
        );
        $roles = [ 'admin', 'somegroup' ];

        return array(
            [ $acl, $roles, true ],
            [ $acl, $roles, false ]
        );
    }
}
