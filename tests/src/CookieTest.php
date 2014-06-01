<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 6:53 PM
 */

namespace tests;


use ptejada\uFlex\Cookie;

class CookieTest extends \PHPUnit_Framework_TestCase {

    public function testInitialization()
    {
        $_SERVER['SERVER_NAME'] = 'localhost';

        ob_start();
        $cookie = new Cookie('test',time());
        $this->assertTrue($cookie->add(), 'Cookie was set correctly');
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertNotEmpty($output);
        $this->assertEquals(0, strpos($output, '<script>'));
    }
}
 