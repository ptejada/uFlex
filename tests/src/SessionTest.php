<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 5:58 PM
 */

namespace tests;


use ptejada\uFlex\Session;

class SessionTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        // Initializes the global session array
        $_SESSION = array();
    }
    public function testInitialization()
    {
        // Manage the whole session
        $session = new Session();
        $this->assertEquals(array('_ip'=>null), $session->toArray());
        $this->assertFalse($session->log->hasError());

        //Manage namespace on the SESSION
        $this->assertArrayNotHasKey('test', $_SESSION, 'The session test namespace should not exists');
        $session = new Session('test');
        $this->assertArrayHasKey('test', $_SESSION);
        $this->assertFalse($session->log->hasError());
    }

    public function testSettersAndGetters()
    {
        $session = new Session('test');
        $session->test = 'Hello World';
        $this->assertEquals('Hello World', $session->test);
        $this->assertEquals('Hello World', $_SESSION['test']['test']);

        // Tests chaining lists
        $session->list = array();
        $session->list->one = 1;
        $session->list->two = 2;

        $this->assertEquals($_SESSION['test']['list'], $session->list->toArray());
    }

}
 