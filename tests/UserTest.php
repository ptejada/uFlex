<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/29/14
 * Time: 11:23 PM
 */

namespace tests;


use Ptejada\UFlex\User;

class UserTest extends \PHPUnit_Framework_TestCase {
    /** @var User  */
    public $user;

    public function setUp()
    {
        // Instantiate the global session variable
        $_SESSION = array();
        // Instantiate the global cookie variable
        $_COOKIE = array();

        $this->user = new User();
        $this->user->config->database->dsn = 'sqlite::memory:';

        $this->user->start();

        // Creates the table
        $this->user->table->runQuery("
            CREATE TABLE IF NOT EXISTS _table_ (
              `user_id` int(7),
              `username` varchar(15) NOT NULL,
              `password` varchar(40) ,
              `email` varchar(35) ,
              `activated` tinyint(1) NOT NULL DEFAULT '0',
              `confirmation` varchar(35) ,
              `reg_date` int(11) ,
              `last_login` int(11) NOT NULL DEFAULT '0',
              PRIMARY KEY (`user_id`)
            )
        ");

        //Create user
        $this->user->table->runQuery('
            INSERT INTO _table_(`user_id`, `username`, `password`, `email`, `activated`, `reg_date`)
            VALUES (1,"pablo","18609a032b2504973748587e8c428334","pablo@live.com",1,1361145707)
        ');
    }

    public function testDefaultInitialization()
    {
        $this->user->login();
        $this->assertFalse($this->user->isSigned());
    }

    public function testLoginFromSession()
    {
        $_SESSION['userData'] = array(
            'data' => array(
                'user_id' => 1,
            ),
            'update' => true,
            'signed' => true,
        );

        $this->user->login();

        $this->assertFalse($this->user->log->hasError());
        $this->assertTrue($this->user->isSigned());

        $this->assertGreaterThanOrEqual(5, count($this->user->session->data->toArray()));
        $this->assertNotEmpty($this->user->username);
        $this->assertNotEmpty($this->user->password);
        $this->assertNotEmpty($this->user->email);

        $this->assertTrue($this->user->isSigned());
    }

    public function testLoginFromCookies()
    {
        $_COOKIE['auto'] = '130118609a032b973748587e8c42833465498745';

        ob_start();
        $this->user->login();
        $output = ob_get_contents();
        ob_end_clean();

        $this->assertFalse($this->user->log->hasError());

        // Expect the autologin cookie to be set
        $this->assertNotEmpty($output);
        $this->assertEquals(0, strpos($output, '<script>'));

        $this->assertGreaterThanOrEqual(5, count($this->user->session->data->toArray()));
        $this->assertNotEmpty($this->user->username);
        $this->assertNotEmpty($this->user->password);
        $this->assertNotEmpty($this->user->email);

        $this->assertTrue($this->user->isSigned());
    }

    public function testLoginWithCredentials()
    {
        $this->user->login();

        $this->assertFalse($this->user->log->hasError());

        $this->user->login('pablo', 123);
        $this->assertTrue($this->user->log->hasError());

        $this->user->login('pablo', 1234);
        $this->assertFalse($this->user->log->hasError());
    }

    public function testFieldValidation()
    {
        $userInfo = $this->getUserInfo();

        $userInfo['username'] = md5(time());
        $success = $this->user->register($userInfo);
        $this->assertEquals($success, !$this->user->log->hasError());
        $this->assertFalse($success);

        $userInfo['username'] = 'user 1';
        $success = $this->user->register($userInfo);
        $this->assertEquals($success, !$this->user->log->hasError());
        $this->assertFalse($success);

        $userInfo['username'] = 'u1';
        $success = $this->user->register($userInfo);
        $this->assertEquals($success, !$this->user->log->hasError());
        $this->assertFalse($success);

        $this->user->addValidation('username', '2-15');
        $success = $this->user->register($userInfo);
        $this->assertEquals($success, !$this->user->log->hasError());
        $this->assertTrue($success);

        $this->assertEquals(array(), $this->user->log->getErrors());
    }

    public function testRegisterNewAccount()
    {
        $userInfo = $this->getUserInfo(2);

        $success = $this->user->register($userInfo);

        $this->assertEquals($success, !$this->user->log->hasError());
        $this->assertTrue($success);

        $this->assertFalse($this->user->isSigned());
        $this->user->login($userInfo['username'], $userInfo['password']);
        $this->assertTrue($this->user->isSigned());

        $this->assertNotEmpty($this->user->username);
        $this->assertNotEmpty($this->user->password);
        $this->assertNotEmpty($this->user->email);
    }

    public function testUserUpdate()
    {
        $this->user->login('pablo', 1234);

        $this->assertFalse($this->user->log->hasError());
        $this->assertEmpty($this->user->session->update);

        $newEmail = 'jose@live.com';
        $this->user->update(array('email'=>$newEmail));

        $this->assertTrue($this->user->session->update);
        $this->assertEquals($newEmail, $this->user->email);
        $this->assertNotEmpty($this->user->username);
    }

    public function testResetPassword()
    {
        $this->user->login();

        $this->assertFalse($this->user->log->hasError());

        $result = $this->user->resetPassword('jose@live.com');
        $this->assertFalse($result);
        $this->assertTrue($this->user->log->hasError());

        $result = $this->user->resetPassword('pablo@live.com');
        $this->assertInstanceOf('Ptejada\UFlex\Collection',$result);
        $this->assertFalse($this->user->log->hasError());

        $this->assertEquals('pablo', $result->username);
        $this->assertEquals('pablo@live.com', $result->email);
        $this->assertEquals(1, $result->user_id);
        $this->assertEquals(40, strlen($result->confirmation));

        // Confirm confirmation was saved on the on DB
        $user = $this->user->table->getRow(array('user_id'=>1));
        $this->assertEquals($user->confirmation, $result->confirmation);
    }

    public function testNewPassword()
    {
        $this->user->login('pablo', 456);
        $this->assertTrue($this->user->log->hasError());
        $this->assertFalse($this->user->isSigned());

        $result = $this->user->resetPassword('pablo@live.com');
        $this->assertInstanceOf('Ptejada\UFlex\Collection',$result);
        $this->assertFalse($this->user->log->hasError());

        $newPassword = array(
            'password' => 456,
            'password2' => 789,
        );

        $this->user->newPassword($result->confirmation, $newPassword);
        $this->assertTrue($this->user->log->hasError());

        $newPassword['password2'] = 456;
        $this->user->newPassword('c4504f0b39c478f39c4badbf74ddbaedf71ecfae' , $newPassword);
        $this->assertTrue($this->user->log->hasError());

        $this->user->newPassword($result->confirmation, $newPassword);
        $this->assertFalse($this->user->log->hasError());

        // Test the new login credentials
        $this->user->login('pablo', 456);
        $this->assertFalse($this->user->log->hasError());
        $this->assertTrue($this->user->isSigned());
    }

    public function testLogout()
    {
        $this->user->login('pablo', 1234);
        $this->assertFalse($this->user->log->hasError());
        $this->assertTrue($this->user->isSigned());

        $this->user->logout();
        $this->assertFalse($this->user->isSigned());
    }

    public function testManageUser()
    {
        $this->user->register($this->getUserInfo(5));

        $this->user->login('pablo', 1234);
        $this->assertFalse($this->user->log->hasError());
        $this->assertTrue($this->user->isSigned());

        $user = $this->user->manageUser(5);
        $this->assertInstanceOf('\Ptejada\UFlex\User', $user);

        $this->assertNotEquals('jose', $user->username);
        $result = $user->update(array('username'=>'jose'));
        $this->assertTrue($result);
        $this->assertEquals('jose', $user->username);

        // Reload the user from the DB to confirm update
        $user = $this->user->manageUser(5);
        $this->assertEquals('jose', $user->username);

        // confirm the main user was not affected
        $this->assertEquals('pablo', $this->user->username);
    }

    protected function getUserInfo($id=0)
    {
        return array(
            'user_id' => $id ? $id : rand(),
            'username' => 'user' . rand(),
            'password' => substr(md5(rand()), 0, 7),
            'email'   => substr(md5(rand()), 0, 5) . '@' . substr(md5(rand()), 0, 8) . '.com',
        );
    }

    protected function tearDown()
    {
        $this->user = null;
    }


}
 