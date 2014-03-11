<?php

namespace Ptejada\UFlex;

class DB
{
    /** @var string - The server IP or host name */
    private $host = '';
    /** @var string - The server user to login as */
    private $user = '';
    /** @var string - The user password */
    private $password = '';
    /** @var string - The name of the database */
    private $dbName = '';
    /** @var string - Alternative DSN string */
    private $dsn = '';

    /** @var \PDO - The DB connection session */
    private $connection;

    /** @var  Log - Log errors and report */
    public $log;

    /**
     * @param string $hostOrDSN - The domain/IP of the DB or the PDO DSN string
     * @param string $dbNameOrUser
     * @param string $userOrPassword
     * @param string $password
     */
    public function __construct($hostOrDSN='', $dbNameOrUser='', $userOrPassword='', $password='')
    {
        if (!$password) {
            // add full DSN string
            $this->dsn = $hostOrDSN;
            $this->user = $dbNameOrUser;
            $this->password = $userOrPassword;
        } else {
            // Add the default DB credentials for MySQL
            $this->host = $hostOrDSN;
            $this->dbName = $dbNameOrUser;
            $this->user = $userOrPassword;
            $this->password = $password;
        }

        $this->log = new Log('DB');
    }

    /**
     * Generate the DSN string for the connection
     * @return string
     */
    protected function generateDSN()
    {
        if ( ! $this->dsn ) {
            $this->dsn = "mysql:dbname={$this->dbName};host={$this->host}";
        }

        return $this->dsn;
    }

    /**
     * Gets the connecting to the database
     * Check if the database connection exists if not connects to the database
     *
     * @return \PDO | bool
     */
    public function getConnection()
    {
        if ( ! ($this->log instanceof Log) ) {
            $this->log = new Log('DB');
        }

        $this->log->channel('Connection');

        // Use cached connection if already connected to server
        if ($this->connection instanceof \PDO) {
            return $this->connection;
        }

        $this->log->report("Connecting to database...");

        try{
            $this->connection = new \PDO($this->generateDSN(), $this->user, $this->password);
            $this->log->report("Connected to database.");
        } catch ( \PDOException $e ){
            $this->log->error("Failed to connect to database, [SQLSTATE] " . $e->getCode());
        }

        // Check is the connection to server succeed
        if ($this->connection instanceof \PDO) {
            return $this->connection;
        } else {
            // There was an error connecting to the DB server
            return false;
        }
    }

    /**
     * Get table object
     *
     * @param $tableName
     *
     * @return DBTAble
     */
    public function getTable($tableName)
    {
        return new DBTAble($this, $tableName);
    }

    /**
     * @param string $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @param string $dbName
     */
    public function setDbName($dbName)
    {
        $this->dbName = $dbName;
    }

    /**
     * Get the record of the last inserted record
     * @return int
     */
    public function getLastInsertedID(){
        return $this->getConnection()->lastInsertId();
    }
}
