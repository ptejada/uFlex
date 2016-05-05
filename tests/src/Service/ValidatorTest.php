<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 5/4/2016
 * Time: 9:02 PM
 */

namespace tests\ptejada\uFlex\Service;


use ptejada\uFlex\Config;
use ptejada\uFlex\Exception\ValidationException;
use ptejada\uFlex\Service\Validator;

class ValidatorTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
    }

    public function testValidateSuccess()
    {
        $validator = Config::getValidator();
        $validator->validate('Username', 'user1');
        $validator->validate('Password', 'pass123');
        $validator->validate('Email', 'user1@test.com');
    }

    public function invalidUsernameProvider()
    {
        return array(
            array(ValidationException::ERROR_MINIMUM, ''),
            array(ValidationException::ERROR_MINIMUM, ' '),
            array(ValidationException::ERROR_MINIMUM, '123'),
            array(ValidationException::ERROR_MAXIMUM, 'user123name456to_use'),
            array(ValidationException::ERROR_PATTERN, 'user name'),
            array(ValidationException::ERROR_PATTERN, 'user-name'),
            array(ValidationException::ERROR_PATTERN, 'user@name'),
            array(ValidationException::ERROR_PATTERN, ' user'),
            array(ValidationException::ERROR_PATTERN, 'user '),
            array(ValidationException::ERROR_PATTERN, '     '),
        );
    }

    /**
     * @param $code
     * @param $username
     *
     * @throws ValidationException
     * @dataProvider invalidUsernameProvider
     */
    public function testValidateUsernameFailure($code, $username)
    {
        $this->setExpectedException('\ptejada\uFlex\Exception\ValidationException', '', $code);
        $validator = Config::getValidator();
        $validator->validate('Username', $username);
    }

    public function validUsernameProvider()
    {
        return array(
            array('user1'),
            array('user_name_valid'),
            array('1train'),
            array('NameToLogin'),
        );
    }

    /**
     * @param $username
     *
     * @dataProvider validUsernameProvider
     * @throws ValidationException
     */
    public function testValidateUsernameSuccess($username)
    {
        $validator = Config::getValidator();
        $validator->validate('Username', $username);
    }

    public function testCustomRule()
    {
        $validator = new Validator();
        $validator->addRule('Phone', array('pattern' => '/^\d{10}$/'));
        $validator->validate('Phone', '2125416540');

        try{
            $validator->validate('Phone', '212-541-6540');
        } catch (ValidationException $e){
            $this->assertEquals(ValidationException::ERROR_PATTERN, $e->getCode());
        }
    }

    public function testUpdateRule()
    {
        $validator = new Validator();
        // Valid user name
        $validator->validate('Username', 'user');

        $validator->getFieldRules('Username')->min = 5;

        try{
            // Same user no longer valid
            $validator->validate('Username', 'user');
        } catch (ValidationException $e){
            $this->assertEquals(ValidationException::ERROR_MINIMUM, $e->getCode());
        }
    }

    public function testValidateAll()
    {
        $validator = new Validator();

        $data = array(
            'Username' => 'user1',
            'Password' => 'p@ssw0rd',
            'Email'    => 'user1@test.com',
            'Email2'   => 'user1@test.com',
        );

        $validator->addRule('Email2', array());
        $validator->getFieldRules('Email')->match = 'Email2';

        $validator->validateAll($data);
    }
}
