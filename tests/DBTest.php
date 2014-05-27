<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests;

use ptejada\uFlex\DB;

class DBTest extends \PHPUnit_Framework_TestCase {

    /** @var  DB */
    protected static $db;

    public function testConnection()
    {
        $db = new DB('sqlite::memory:');

        $this->assertInstanceOf('PDO', $db->getConnection(), 'Successfully connects to DB');
        $this->assertFalse($db->log->hasError(), 'There should be no error');
        $this->assertNotEmpty($db->log->getReports(), 'There should some report entries');
        $this->assertEquals(3, count($db->log->getReports()), 'Expect exactly 3 report after connection');
    }

    /*
    public function testConnectionFails()
    {
        $db = new DB('localhost','test','root','');

        $this->assertNotInstanceOf('PDO', $db->getConnection(), 'Fails to connects to DB');
        $this->assertTrue($db->log->hasError(), 'There should be errors');
        $this->assertNotEmpty($db->log->getErrors(), 'There should some report entries');
        $this->assertEquals(3, count($db->log->getReports()), 'Expect exactly 3 reports after connection fails');
    }
    */

    public function testGetTable()
    {
        $db = new DB('sqlite::memory:');
        $table = $db->getTable('Users');

        $this->assertInstanceOf('ptejada\uFlex\DB_TAble', $table, 'Should be an instance of DBTable');
    }

}
 