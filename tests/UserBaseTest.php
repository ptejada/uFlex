<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 3:38 PM
 */

namespace tests;


use ptejada\uFlex\UserBase;

class UserBaseTest extends \PHPUnit_Framework_TestCase {


    public function setUp()
    {
        // Instantiate the global session variable
        $_SESSION = array();
    }
    public function testSettersAndGetters()
    {
        $user = new UserBase(array(
            'Username' => 'Pablo',
            'Password' => 'password',
            'RegDate' => 1396148789,
        ));

        $this->assertEquals('Pablo', $user->Username);
        $this->assertEquals('password', $user->Password);
        $this->assertEquals(1396148789, $user->RegDate);

        $this->assertInstanceOf('ptejada\uFlex\Collection', $user->config);
        $this->assertInstanceOf('ptejada\uFlex\Collection', $user->config->userDefaultData);
        $this->assertInstanceOf('ptejada\uFlex\Collection', $user->config->database);

        $user->config->userDefaultData->update(array(
            'Username' => 'Anonimo'
        ));

        $this->assertEquals('Anonimo', $user->config->userDefaultData->Username);

    }

    public function testValidations()
    {
        $user = new UserBase(array(
            'Username' => 'Pablo',
            'Password' => 'password',
            'RegDate' => 1396148789,
        ));

        $user->Username = 'PabloTejada';
        $user->addValidation('Username','1-5');

        $this->assertFalse($user->log->hasError());
    }
}
 