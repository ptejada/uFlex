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

class LogTest extends \PHPUnit_Framework_TestCase {
    /** @var  Log */
    protected $log;

    protected function setUp()
    {
        parent::setUp();
        $this->log = Config::getLog();
    }

    public function testDefaultSection()
    {
        $this->assertEquals('init', $this->log->getSection(), 'Default default namespace');
    }

    public function testChangingSection()
    {
        $this->log->error('Hello World');
        $this->assertTrue($this->log->hasError(), 'The test default channel has error');
    }
}
