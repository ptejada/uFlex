<?php
namespace tests;

use PHPUnit_Extensions_Database_DataSet_IDataSet;

abstract class Tests_DatabaseTestCase extends \PHPUnit_Extensions_Database_TestCase
{
    // only instantiate pdo once for test clean-up/fixture load
    static private $pdo = null;

    // only instantiate PHPUnit_Extensions_Database_DB_IDatabaseConnection once per test
    private $conn = null;

    protected $fixture = 'users';

    /**
     * @return \PDO
     */
    public function getPDO()
    {
        if (is_null(self::$pdo)) {
            $this->getConnection();
        }
        return self::$pdo;
    }

    final public function getConnection()
    {
        if ($this->conn === null) {
            if (self::$pdo == null) {
                self::$pdo = new \PDO( $GLOBALS['DB_DSN'], $GLOBALS['DB_USER'], $GLOBALS['DB_PASSWD'] );
            }
            $this->conn = $this->createDefaultDBConnection(self::$pdo, $GLOBALS['DB_DBNAME']);
        }

        return $this->conn;
    }

    /**
     * Returns the test dataset.
     *
     * @return PHPUnit_Extensions_Database_DataSet_IDataSet
     */
    protected function getDataSet()
    {
        $file = dirname(__FILE__) . "/fixtures/{$this->fixture}.xml";
        return $this->createMySQLXMLDataSet($file);
    }
}