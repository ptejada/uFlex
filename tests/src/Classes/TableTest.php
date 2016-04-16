<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/7/14
 * Time: 10:54 PM
 */

namespace tests\ptejada\uFlex\Classes;

use ptejada\uFlex\Config;
use tests\Tests_DatabaseTestCase;

class TableTest extends Tests_DatabaseTestCase {

    public function testFull()
    {
        $table = Config::getConnection()->getTable('Users');

        $this->assertInstanceOf('ptejada\uFlex\Classes\Table', $table, 'Should be an instance of DBTable');
        $this->assertInstanceOf('ptejada\uFlex\Service\Log', $table->log);

        for($i=2; $i<5; $i++)
        {
            $table->runQuery("INSERT INTO _table_(ID, Username) VALUES($i, 'user$i')");
            $this->assertEquals($i, $table->getLastInsertedID(), 'Confirms last inserted record ID');
        }

        // Get a record
        $user = $table->getRow(array('ID'=>1));

        $this->assertInstanceOf('ptejada\uFlex\Classes\Collection', $user, 'Retrieve a record from the table');

        // Both console log should be equal
        $this->assertEquals(Config::getConnection()->log->getReports(), $table->log->getReports());
    }

}
