<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests;

use Ptejada\UFlex\DB;

class DBTest extends \PHPUnit_Framework_TestCase {

    /** @var  DB */
    protected static $db;

    public static function setUpBeforeClass()
    {
        $db = self::$db = new DB('sqlite::memory:');
        //$pdo = $db->getConnection();

        //$pdo->exec(file_get_contents(dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'uFlex_database.sql'));
    }

    public function testConnection()
    {
        $db = new DB('sqlite::memory:');

        $this->assertInstanceOf('PDO', $db->getConnection(), 'Successfully connects to DB');
        $this->assertFalse($db->log->hasError(), 'There should be no error');
        $this->assertNotEmpty($db->log->getReports(), 'There should some report entries');
        $this->assertEquals(2, count($db->log->getReports()), 'Expect exactly 2 report after connection');
    }

    public function testConnectionFails()
    {
        $db = new DB('localhost','test','root','');

        $this->assertNotInstanceOf('PDO', $db->getConnection(), 'Fails to connects to DB');
        $this->assertTrue($db->log->hasError(), 'There should be errors');
        $this->assertNotEmpty($db->log->getErrors(), 'There should some report entries');
        $this->assertEquals(2, count($db->log->getReports()), 'Expect exactly 2 reports after connection fails');
    }

    public function testGetTable()
    {
        $db = new DB('sqlite::memory:');
        $table = $db->getTable('Users');

        $this->assertInstanceOf('Ptejada\UFlex\DBTAble', $table, 'Should be an instance of DBTable');
    }

}
 