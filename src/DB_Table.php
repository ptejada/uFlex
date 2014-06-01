<?php

namespace ptejada\uFlex;

/**
 * Class DB_Table
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class DB_Table
{
    /** @var  Log - Log errors and report */
    public $log;
    /** @var string - The table name */
    private $tableName = '';
    /** @var DB - The DB connection session */
    private $db;

    /**
     * Initializes a database table object
     *
     * @param DB     $db
     * @param string $table
     */
    public function __construct(DB $db, $table)
    {
        $this->db = $db;
        $this->log = $db->log;
        $this->tableName = $table;
    }

    /**
     * Test field value in database
     * Check for the uniqueness of a value in a specified field/column.
     * For example could be use to check for the uniqueness of a Username
     * or email prior to registration
     *
     * @param string      $field       The name of the field
     * @param string|int  $val         The value for the field to check
     * @param bool|string $customError Error string to log if field value is not unique
     *
     * @return bool
     */
    public function isUnique($field, $val, $customError = false)
    {
        $row = $this->getRow(array($field => $val));

        if ($row) {
            $this->log->report("There was a match for $field = $val");
            $this->log->formError($field, $customError ? $customError : "The {$field} {$val} exists in database");
            return true;
        } else {
            $this->log->report("No Match for $field = $val");
            return false;
        }
    }

    /**
     * Get a single row from the table depending on arguments
     *
     * @param array $arguments -  field and value pair set to look up user for
     *
     * @return bool|Collection
     */
    public function getRow($arguments)
    {
        $sql = 'SELECT * FROM _table_ WHERE _arguments_ LIMIT 1';

        if (!$stmt = $this->getStatement($sql, $arguments)) {
            // Something went wrong executing the SQL statement
            return false;
        } else {
            return $stmt->fetch();
        }

    }

    /**
     * Get a PDO statement
     *
     * @param string       $sql  SQL query string
     * @param bool|mixed[] $args argument to execute the statement with
     *
     * @return bool|\PDOStatement
     */
    public function getStatement($sql, $args = false)
    {
        // The parsed sql statement
        $query = $this->buildQuery($sql, $args);

        if ($connection = $this->db->getConnection()) {
            //Prepare the statement
            if ($stmt = $connection->prepare($query)) {
                //Log the SQL Query first
                $this->log->report("SQL Statement: {$query}");

                // When fetched return an object
                $stmt->setFetchMode(\PDO::FETCH_INTO, new Collection());

                // If arguments were passed execute the statement
                if ($args) {
                    $this->log->report("SQL Data Sent: [" . implode(', ', $args) . "]");
                    $stmt->execute($args);
                }

                // Handles any error during execution
                if ($stmt->errorCode() > 0) {
                    $error = $stmt->errorInfo();
                    $this->log->error("PDO({$error[0]})[{$error[1]}] {$error[2]}");
                    return false;
                }

                return $stmt;
            } else {
                $this->log->error('Failed to create a PDO statement with: ' . $query);
                return false;
            }
        } else {
            // Failed to connect to the database
            return false;
        }
    }

    /**
     * Builds a query string with the passed arguments
     *
     * @param string $sql
     * @param array  $arguments - Associative array of fields and values
     *
     * @return string
     */
    private function buildQuery($sql, $arguments = null)
    {
        if (is_array($arguments)) {
            $finalArgs = array();
            foreach ($arguments as $field => $val) {
                // Parametrize the arguments
                $finalArgs[] = " {$field}=:{$field}";
            }

            // Join all the arguments as a string
            $finalArgs = implode(' AND', $finalArgs);

            if (strpos($sql, ' _arguments_')) {
                // Place the arguments string in the placeholder
                $sql = str_replace(' _arguments_', $finalArgs, $sql);
            } else {
                // Appends the parameters string the sql query
                // $sql .= $finalArgs; TODO: Watch this expression if it is on use
            }
        }

        //Replace the _table_ placeholder
        $sql = str_replace(' _table_', " {$this->tableName} ", $sql);

        return $sql;
    }

    /**
     * Query the table
     *
     * @param      $sql
     * @param bool $arguments
     *
     * @return bool|\PDOStatement
     */
    public function query($sql, $arguments = false)
    {
        if (!$stmt = $this->getStatement($sql, $arguments)) {
            // Something went wrong executing the SQL statement
            return false;
        } else {
            return $stmt;
        }
    }

    /**
     * Executes SQL query and checks for success
     *
     * @param string     $sql       -  SQL query string
     * @param array|bool $arguments -  Array of arguments to execute $sql with
     *
     * @return bool
     */
    public function runQuery($sql, $arguments = false)
    {
        if (!$stmt = $this->getStatement($sql, $arguments)) {
            // Something went wrong executing the SQL statement
            return false;
        }

        // If there are no arguments, execute the statement
        if (!$arguments) {
            $stmt->execute();
        }

        $rows = $stmt->rowCount();
        $rows = ($rows > 0) ? $rows : count($stmt->fetchAll());

        if ($rows > 0) {
            //Good, Rows where affected
            $this->log->report("$rows row(s) where Affected");
            return true;
        } else {
            //Bad, No Rows where Affected
            $this->log->report('No rows were Affected');
            return false;
        }
    }

    /**
     * Get the ID of the last inserted record
     *
     * @return int
     */
    public function getLastInsertedID()
    {
        return $this->db->getLastInsertedID();
    }
}
