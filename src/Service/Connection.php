<?php

namespace ptejada\uFlex\Service;

use ptejada\uFlex\Classes\Table;
use ptejada\uFlex\Config;

/**
 * Database Connection Manager
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class Connection
{
    /**
     * Log errors and report
     * @deprecated
     * @var  Log
     */
    public $log;
    /** @var string - The server IP or host name */
    private $host = 'localhost';
    /** @var string - The server user to login as */
    private $user = 'root';
    /** @var string - The user password */
    private $password = '';
    /** @var string - The name of the database */
    private $dbName = '';
    /** @var string - Alternative DSN string */
    private $dsn = '';
    /** @var \PDO - The DB connection session */
    private $pdo;

    /**
     * Initializes the Database object
     *
     * @param string $hostOrDSN|\PDO - The domain/IP of the DB, the PDO DSN string or PDO connection
     * @param string $dbName    - The name of the database
     */
    public function __construct($hostOrDSN = '', $dbName = '')
    {
        if (empty($hostOrDSN) && empty($dbName)) {
            $options = Config::get('connection');

            $this->host     = $options->host;
            $this->name     = $options->name;
            $this->user     = $options->user;
            $this->password = $options->password;
            $this->dsn      = $options->dsn;
            $this->pdo      = $options->pdo;
        } else {
            if (!$dbName) {
                if ($hostOrDSN instanceof \PDO) {
                    // Saves the PDO connection
                    $this->setConnection($hostOrDSN);
                } else {
                    // add full DSN string
                    $this->dsn = $hostOrDSN;
                }
            } else {
                // Add the default DB credentials for MySQL
                $this->host = $hostOrDSN;
                $this->dbName = $dbName;
            }
        }

        $this->log = Config::getLog();
    }

    /**
     * Get table object
     *
     * @param $tableName
     *
     * @return Table
     */
    public function getTable($tableName)
    {
        return new Table($this, $tableName);
    }

    /**
     * Set the database username
     *
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * Set the database user password
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * Set the name of the Database to connect to
     * @param string $dbName
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * Get the record of the last inserted record
     *
     * @return int
     */
    public function getLastInsertedID()
    {
        return $this->getConnection()->lastInsertId();
    }

    /**
     * Gets the connecting to the database
     * Check if the database connection exists if not connects to the database
     *
     * @return \PDO | bool
     */
    public function getConnection()
    {
        // Use cached connection if already connected to server
        if ($this->pdo instanceof \PDO) {
            return $this->pdo;
        }

        $this->log->section('db')->debug('Connecting to database...');

        try{
            $this->pdo = new \PDO($this->generateDSN(), $this->user, $this->password);
            $this->log->section('db')->debug('Connected to database.');
        } catch ( \PDOException $e ){
            $this->log->section('db')->error('Failed to connect to database, [SQLSTATE] ' . $e->getCode());
        }

        // Check is the connection to server succeed
        if ($this->pdo instanceof \PDO) {
            return $this->pdo;
        } else {
            // There was an error connecting to the DB server
            return false;
        }
    }

    /**
     * Generate the DSN string for the connection
     *
     * @return string
     */
    protected function generateDSN()
    {
        if (!$this->dsn) {
            $this->dsn = "mysql:dbname={$this->dbName};host={$this->host}";
        }

        return $this->dsn;
    }

    /**
     * Set the connection
     *
*@param \PDO $pdo
     */
    public function setConnection(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }
}
