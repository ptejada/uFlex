<?php

namespace Ptejada\UFlex;

class UserBase
{
    /** @var  Log - Log errors and report */
    public $log;
    
    /** @var DB - The database connection */
    protected $db;

    /** @var  Cookie - The cookie for autologin */
    protected $cookie;

    /** @var  Session - The namespace session object */
    public $session;

    /** @var  Hash - Use to generate hashes */
    protected $hash;
    
    /** @var array - The user information object */
    protected $_data;

    /** @var Collection - Updates for the user information object */ 
    protected $_updates;
    
    /** @var Collection - default field validations*/
    protected $_validations = array(
        'username' => array(
            'limit' => '3-15',
            'regEx' => '/^([a-zA-Z0-9_])+$/'
        ),
        'password' => array(
            'limit' => '3-15',
            'regEx' => ''
        ),
        'email'    => array(
            'limit' => '4-45',
            'regEx' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i'
        )
    );

    /** @var Collection - Class configuration options */
    public $config = array(
        'cookieTime'      => '30',
        'cookieName'      => 'auto',
        'cookiePath'      => '/',
        'cookieHost'      => false,
        'userTableName'   => 'users',
        'userSession'     => 'userData',
        'userDefaultData' => array(
            'username' => 'Guess',
            'user_id'  => 0,
            'password' => 0,
            'signed'   => false
        ),
        'database' => array(
            'host'  =>  'localhost',
            'name'  =>  '',
            'user'  =>  '',
            'password'  =>  '',
            'dsn'  =>  '',
        )
    );

    public function __construct(array $userData = array())
    {
        // Instantiate the logger
        $this->log = new Log('User');

        // Instantiate the hash generator
        $this->hash = new Hash();

        // Convert the configurations to a collection
        $this->config = new Collection(array_merge(array(), (array) $this->config));

        // Convert the validations rule to a collection
        $this->_validations = new Collection(array_merge(array(), (array) $this->_validations));

        // Hydrate the model with user information
        $this->_data = $userData;
    }

    /**
     * Adds validation to queue for either the Registration or Update Method
     * Single Entry:
     * <pre>
     *  Requires the first two parameters
     *        $name  = string (name of the field to be validated)
     *        $limit = string (range of the accepted value length in the format of "5-10")
     *            - to make a field optional start with 0 (Ex. "0-10")
     *    Optional third parameter
     *        $regEx = string (Regular Expression to test the field)
     * </pre>
     * _____________________________________________________________________________________________________
     * Multiple Entry:
     * <pre>
     *    Takes only the first argument
     *        $name = Array Object (takes an object in the following format:
     *            array(
     *                "username" => array(
     *                        "limit" => "3-15",
     *                        "regEx" => "/^([a-zA-Z0-9_])+$/"
     *                        ),
     *                "password" => array(
     *                        "limit" => "3-15",
     *                        "regEx" => false
     *                        )
     *                );
     * </pre>
     *
     * @access public
     * @api
     *
     * @param string|array $name  Name of the field to validate or an array of all the fields and their validations
     * @param string       $limit A range of the accepted value length in the format of "5-10",
     *                            to make a field optional start with 0 (Ex. "0-10")
     * @param string|bool  $regEx Regular expression to the test the field with
     *
     * @return null
     */
    public function addValidation($name, $limit = '0-1', $regEx = false)
    {
        $this->log->channel('validation');
        if (is_array($name)) {
            $this->_validations->update($name);
            $this->log->report('New Validation Object added');
        } else {
            $this->_validations->$name = array(
                'limit' => $limit,
                'regEx' => $regEx,
            );
            $this->log->report("The $name field has been added for validation");
        }
    }

    /**
     * Validates All fields in _updates queue
     */
    protected function validateAll()
    {
        foreach ($this->_updates->getAll() as $field => $val) {
            //Match double fields
            $field2 = $field . '2';
            if (!is_null($this->_updates->$field2)) {
                // Compared the two double fields
                if ($val != $this->_updates->$field2) {
                    $this->log->formError($field, ucfirst($field) . "s did not match");
                } else {
                    $this->log->report(ucfirst($field) . "s matched");
                }
            }

            // Trim white spaces at end and start
            $this->_updates->$field = trim($val);

            // Check if a validation rule exists for the field
            if ( $validation = $this->_validations->$field) {
                $this->validate($field, $validation->limit, $validation->regEx);
            }
        }
        return ! $this->log->hasError();
    }

    /**
     * Validates a field in tmp_data
     *
     * @param string      $name  field name
     * @param string      $limit valid value length range, Ex: '0-10'
     * @param bool|string $regEx regular expression to test the field against
     *
     * @return bool
     */
    protected function validate($name, $limit, $regEx = false)
    {
        $Name = ucfirst($name);
        $value = $this->_updates->$name;
        $length = explode('-', $limit);
        $min = intval($length[0]);
        $max = intval($length[1]);

        if (!$max and !$min) {
            $this->log->error("Invalid second parameter for the $name validation");
            return false;
        }

        if (!$value) {
            if (is_null($value)) {
                $this->log->report("missing index $name from the POST array");
            }
            if (strlen($value) == $min) {
                $this->log->report("$Name is blank and optional - skipped");
                return true;
            }
            $this->log->formError($name, "$Name is required.");
            return false;
        }

        // Validate the value maximum length
        if (strlen($value) > $max) {
            $this->log->formError($name, "The $Name is larger than $max characters.");
            return false;
        }

        // Validate the value minimum length
        if (strlen($value) < $min) {
            $this->log->formError($name, "The $Name is too short. It should at least be $min characters long");
            return false;
        }

        // Validate the value pattern
        if ($regEx) {
            preg_match($regEx, $value, $match);
            if (preg_match($regEx, $value, $match) === 0) {
                $this->log->formError($name, "The $Name \"{$value}\" is not valid");
                return false;
            }
        }

        /*
         * If the execution reaches this point then the field value
         * is considered to be valid
         */
        $this->log->report("The $name is Valid");
        return true;
    }

    /**
     * Get the value of a user property
     * @param $name
     *
     * @return mixed
     */
    public function getProperty($name)
    {
       return $this->__get($name);
    }

    public function __set($name, $value)
    {
        $this->_updates->$name = $value;
    }

    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            if ($this->_updates->$name) {
                return $this->_updates->$name;
            }
        }

        return null;

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name .
            ' in ' . $trace[0]['file'] .
            ' on line ' . $trace[0]['line'],
            E_USER_NOTICE);

        return null;
    }
}
