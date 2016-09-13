<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/1/14
 * Time: 12:20 PM
 */

namespace tests\ptejada\uFlex\Service;


use ptejada\uFlex\Config;
use ptejada\uFlex\Service\Log;
use ptejada\uFlex\Service\UserTokens;
use tests\Tests_DatabaseTestCase;

class UserTokensTest extends Tests_DatabaseTestCase  {
    protected $fixture = 'usertokens';

    /** @var  UserTokens */
    protected $tokens;

    public function testGenerate()
    {
        $newToken = $this->tokens->generate(1, UserTokens::TYPE_AUTO_LOGIN);
        $this->assertNotEmpty($newToken);
    }

    protected function setUp()
    {
        $this->tokens = new UserTokens();
        parent::setUp();
    }


}
