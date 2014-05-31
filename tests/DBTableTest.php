<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests;

use ptejada\uFlex\DB;

class DBTableTest extends \PHPUnit_Framework_TestCase {

    /** @var  DB */
    protected static $db;

    public static function setUpBeforeClass()
    {
       self::$db = new DB('sqlite::memory:');
    }

    public function testFull()
    {
        $db = self::$db;
        $table = $db->getTable('Users');

        $this->assertInstanceOf('ptejada\uFlex\DB_Table', $table, 'Should be an instance of DBTable');
        $this->assertInstanceOf('ptejada\uFlex\Log', $table->log);

        // Creates the table
        $table->runQuery("
            CREATE TABLE IF NOT EXISTS _table_ (
              `ID` int(7),
              `Username` varchar(15) NOT NULL,
              `Password` varchar(35) ,
              `Email` varchar(35) ,
              `Activated` tinyint(1) NOT NULL DEFAULT '0',
              `Confirmation` varchar(35) ,
              `RegDate` int(11) ,
              `LastLogin` int(11) NOT NULL DEFAULT '0',
              `first_name` varchar(50) ,
              `last_name` varchar(50) ,
              PRIMARY KEY (`ID`)
            )
        ");

        for($i=1; $i<5; $i++)
        {
            $table->runQuery("INSERT INTO _table_(ID, Username) VALUES($i, 'user$i')");
            $this->assertEquals($i, $table->getLastInsertedID(), 'Confirms last inserted record ID');
        }

        // Get a record
        $user = $table->getRow(array('ID'=>1));

        $this->assertInstanceOf('ptejada\uFlex\Collection', $user, 'Retrieve a record from the table');

        // Both console log should be equal
        $this->assertEquals($db->log->getFullConsole(), $table->log->getFullConsole());
    }

}
 