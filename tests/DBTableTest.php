<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests;

use Ptejada\UFlex\DB;

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
        $db->getConnection();
        $table = $db->getTable('Users');

        $this->assertInstanceOf('Ptejada\UFlex\DBTAble', $table, 'Should be an instance of DBTable');
        $this->assertInstanceOf('Ptejada\UFlex\Log', $table->log);

        // Creates the table
        $table->runQuery("
            CREATE TABLE IF NOT EXISTS _table_ (
              `user_id` int(7),
              `username` varchar(15) NOT NULL,
              `password` varchar(35) ,
              `email` varchar(35) ,
              `activated` tinyint(1) NOT NULL DEFAULT '0',
              `confirmation` varchar(35) ,
              `reg_date` int(11) ,
              `last_login` int(11) NOT NULL DEFAULT '0',
              `first_name` varchar(50) ,
              `last_name` varchar(50) ,
              PRIMARY KEY (`user_id`)
            )
        ");

        for($i=1; $i<5; $i++)
        {
            $table->runQuery("INSERT INTO _table_(user_id, username) VALUES($i, 'user$i')");
            $this->assertEquals($i, $table->getLastInsertedID(), 'Confirms last inserted record ID');
        }

        // Get a record
        $user = $table->getRow(array('user_id'=>1));

        $this->assertInstanceOf('StdClass', $user, 'Retrieve a record from the table');

        var_dump($table->log->getFullConsole());
        var_dump($db->log->getFullConsole());
    }

}
 