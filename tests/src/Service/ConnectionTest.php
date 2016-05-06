<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests\ptejada\uFlex\Service;

use ptejada\uFlex\Service\Connection;

class ConnectionTest extends \PHPUnit_Framework_TestCase {

    /** @var  Connection */
    protected static $db;

    public function testConnection()
    {
        $db = new Connection('sqlite::memory:');

        $this->assertInstanceOf('PDO', $db->getConnection(), 'Successfully connects to DB');
        $this->assertFalse($db->log->hasError(), 'There should be no error');
        $this->assertNotEmpty($db->log->getReports(), 'There should some report entries');
    }

    public function testConnectionFails()
    {
        $db = new Connection('localhost','test','root','');

        $this->assertNotInstanceOf('PDO', $db->getConnection(), 'Fails to connects to DB');
        $this->assertTrue($db->log->hasError(), 'There should be errors');
        $this->assertNotEmpty($db->log->getErrors(), 'There should some report entries');
        $this->assertNotEmpty($db->log->getReports(), 'Expect some reports after connection fails');
    }

    public function testGetTable()
    {
        $db = new Connection('sqlite::memory:');
        $table = $db->getTable('Users');

        $this->assertInstanceOf('ptejada\uFlex\Classes\Table', $table, 'Should be an instance of DBTable');
    }

}
