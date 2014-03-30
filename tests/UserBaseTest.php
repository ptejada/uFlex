<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 3:38 PM
 */

namespace tests;


use Ptejada\UFlex\UserBase;

class UserBaseTest extends \PHPUnit_Framework_TestCase {


    public function setUp()
    {
        // Instantiate the global session variable
        $_SESSION = array();
    }
    public function testSettersAndGetters()
    {
        $user = new UserBase(array(
            'username' => 'Pablo',
            'password' => 'password',
            'reg_date' => 1396148789,
        ));

        $this->assertEquals('Pablo', $user->username);
        $this->assertEquals('password', $user->password);
        $this->assertEquals(1396148789, $user->reg_date);

        $this->assertInstanceOf('Ptejada\UFlex\Collection', $user->config);
        $this->assertInstanceOf('Ptejada\UFlex\Collection', $user->config->userDefaultData);
        $this->assertInstanceOf('Ptejada\UFlex\Collection', $user->config->database);

        $user->config->userDefaultData->update(array(
            'username' => 'Anonimo'
        ));

        $this->assertEquals('Anonimo', $user->config->userDefaultData->username);

    }

    public function testValidations()
    {
        $user = new UserBase(array(
            'username' => 'Pablo',
            'password' => 'password',
            'reg_date' => 1396148789,
        ));

        $user->username = 'PabloTejada';
        $user->addValidation('username','1-5');

        $this->assertFalse($user->log->hasError());
    }
}
 