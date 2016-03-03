<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests;

use ptejada\uFlex\Connection;

class DBTableTest extends Tests_DatabaseTestCase {

    /** @var  Connection */
    protected static $db;

    public function setUp()
    {
        self::$db = new Connection($this->getPDO());
        parent::setUp();
    }

    public function testFull()
    {
        $db = self::$db;
        $table = $db->getTable('Users');

        $this->assertInstanceOf('ptejada\uFlex\DB_Table', $table, 'Should be an instance of DBTable');
        $this->assertInstanceOf('ptejada\uFlex\Log', $table->log);

        for($i=2; $i<5; $i++)
        {
            $table->runQuery("INSERT INTO _table_(ID, Username) VALUES($i, 'user$i')");
            $this->assertEquals($i, $table->getLastInsertedID(), 'Confirms last inserted record ID');
        }

        // Get a record
        $user = $table->getRow(array('ID'=>1));

        $this->assertInstanceOf('ptejada\uFlex\Classes\Collection', $user, 'Retrieve a record from the table');

        // Both console log should be equal
        $this->assertEquals($db->log->getFullConsole(), $table->log->getFullConsole());
    }

}
