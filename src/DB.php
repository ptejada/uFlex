<?php

namespace Ptejada\UFlex;

class DB
{
    private $host = '';
    private $user = '';
    private $password = '';
    private $dbName = '';
    private $dsn = '';

    /**
     * Connects to the database
     * Check if the database connection exists if not connects to the database
     *
     * @return bool
     */
    protected function connect()
    {
        if (is_object($this->db)) {
            return true;
        }

        /* Connect to an ODBC database using driver invocation */
        $user = $this->db['user'];
        $pass = $this->db['pass'];
        $host = $this->db['host'];
        $name = $this->db['name'];
        $dsn = $this->db['dsn'];

        if (!$dsn) {
            $dsn = "mysql:dbname={$name};host={$host}";
        }

        $this->report("Connecting to database...");

        try{
            $this->db = new \PDO($dsn, $user, $pass);
            $this->report("Connected to database.");
        } catch ( PDOException $e ){
            $this->error("Failed to connect to database, [SQLSTATE] " . $e->getCode());
        }

        if (is_object($this->db)) {
            return true;
        }
        return false;
    }

    /**
     * Test field value in database
     * Check for the uniqueness of a value in a specified field/column.
     * For example could be use to check for the uniqueness of a username
     * or email prior to registration
     *
     * @param string      $field The name of the field
     * @param string|int  $val   The value for the field to check
     * @param bool|string $err   Custom error string to log if field value is not unique
     *
     * @return bool
     */
    function check_field($field, $val, $err = false)
    {
        $res = $this->getRow(Array($field => $val));

        if ($res) {
            if ($err) {
                $this->form_error($field, $err);
            } else {
                $this->form_error($field, "The $field $val exists in database");
            }
            $this->report("There was a match for $field = $val");
            return true;
        } else {
            $this->report("No Match for $field = $val");
            return false;
        }
    }

    /**
     * Executes SQL query and checks for success
     *
     * @param string     $sql  SQL query string
     * @param bool|array $args Array of arguments to execute $sql with
     *
     * @return bool
     */
    function check_sql($sql, $args = false)
    {
        $st = $this->getStatement($sql);

        if (!$st) {
            return false;
        }

        if ($args) {
            $st->execute($args);
            $this->report("SQL Data Sent: [" . implode(', ', $args) . "]"); //Log the SQL Query first
        } else {
            $st->execute();
        }

        $rows = $st->rowCount();

        if ($rows > 0) {
            //Good, Rows where affected
            $this->report("$rows row(s) where Affected");
            return true;
        } else {
            //Bad, No Rows where Affected
            $this->report("No rows were Affected");
            return false;
        }
    }

    /**
     * Get a single user row depending on arguments
     *
     * @param array $args field and value pair set to look up user for
     *
     * @return bool|mixed
     */
    function getRow($args)
    {
        $sql = "SELECT * FROM :TABLE WHERE :args LIMIT 1";

        $st = $this->getStatement($sql, $args);

        if (!$st) {
            return false;
        }

        if (!$st->rowCount()) {
            $this->report("Query returned empty");
            return false;
        }

        return $st->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Get a PDO statement
     *
     * @param string       $sql  SQL query string
     * @param bool|mixed[] $args argument to execute the statement with
     *
     * @return bool|\PDOStatement
     */
    function getStatement($sql, $args = false)
    {
        if (!$this->connect()) {
            return false;
        }

        if ($args) {
            $finalArgs = array();
            foreach ($args as $field => $val) {
                $finalArgs[] = " {$field}=:{$field}";
            }

            $finalArgs = implode(" AND", $finalArgs);

            if (strpos($sql, " :args")) {
                $sql = str_replace(" :args", $finalArgs, $sql);
            } else {
                $sql .= $finalArgs;
            }
        }

        //Replace the :table placeholder
        $sql = str_replace(" :table ", " {$this->opt["table_name"]} ", $sql);

        $this->report("SQL Statement: {$sql}"); //Log the SQL Query first

        if ($args) {
            $this->report("SQL Data Sent: [" . implode(', ', $args) . "]");
        } //Log the SQL Query first

        //Prepare the statement
        $res = $this->db->prepare($sql);

        if ($args) {
            $res->execute($args);
        }

        if ($res->errorCode() > 0) {
            $error = $res->errorInfo();
            $this->error("PDO({$error[0]})[{$error[1]}] {$error[2]}");
            return false;
        }

        return $res;
    }
}
