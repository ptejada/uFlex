<?php

namespace ptejada\uFlex;

use ptejada\uFlex\Classes\Collection;

/**
 * Class UserBase
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
abstract class AbstractUser
{
    /** @var  Log - Log errors and report */
    public $log;
    /** @var Collection - Class configuration options */
    public $config = array(
        'cookieTime'      => '30',
        'cookieName'      => 'auto',
        'cookiePath'      => '/',
        'cookieHost'      => false,
        'userTableName'   => 'Users',
        'userSession'     => 'userData',
        'userDefaultData' => array(
            'Username' => 'Guess',
            'ID'       => 0,
            'Password' => 0,
        ),
        'database'        => array(
            'host'     => 'localhost',
            'name'     => '',
            'user'     => '',
            'password' => '',
            'dsn'      => '',
            'pdo'      => null,
        )
    );
    /** @var  Hash - Use to generate hashes */
    protected $hash;
    /** @var array - The user information object */
    protected $_data;
    /** @var Collection - Updates for the user information object */
    protected $_updates;
    /** @var Collection - default field validations */
    protected $_validations = array(
        'Username' => array(
            'limit' => '3-15',
            'regEx' => '/^([a-zA-Z0-9_])+$/'
        ),
        'Password' => array(
            'limit' => '3-15',
            'regEx' => ''
        ),
        'Email'    => array(
            'limit' => '4-45',
            'regEx' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,63})$/i'
        )
    );

    /**
     * Initializes the the User object
     *
     * @param array $userData
     */
    public function __construct(array $userData = array())
    {
        // Instantiate the logger
        $this->log = new Log('User');

        // Instantiate the hash generator
        $this->hash = new Hash();

        // Convert the configurations to a collection
        $this->config = new Collection((array) $this->config);

        // Convert the validations rule to a collection
        $this->_validations = new Collection((array) $this->_validations);

        // Hydrate the model with user information
        $this->_data = $userData;

        // Initializes the updates collection
        $this->_updates = new Collection();
    }

    /**
     * Adds validation to queue for either the Registration or Update Method
     * Single Entry:
     *      Requires the first two parameters
     *            $name  = string (name of the field to be validated)
     *            $limit = string (range of the accepted value length in the format of "5-10")
     *                    - to make a field optional start with 0 (Ex. "0-10")
     *       Optional third parameter
     *            $regEx = string (Regular Expression to test the field)
     * _____________________________________________________________________________________________________
     * Multiple Entry:
     *
     *    Takes only the first argument
     *        $name = Array Object (takes an object in the following format:
     *            array(
     *                "Username" => array(
     *                        "limit" => "3-15",
     *                        "regEx" => "/^([a-zA-Z0-9_])+$/"
     *                        ),
     *                "Password" => array(
     *                        "limit" => "3-15",
     *                        "regEx" => false
     *                        )
     *                );
     *
     * @access public
     * @api
     *
     * @param string|array $name  Name of the field to validate or an array of all the fields and their validations
     * @param string       $limit A range of the accepted value length in the format of "5-10", to make a field optional
     *                            start with 0 (Ex. "0-10")
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
     * Get the value of a user property
     *
     * @param $name
     *
     * @return mixed
     */
    public function getProperty($name)
    {
        return $this->__get($name);
    }

    /**
     * Get the value of a user property
     *
     * @param $name
     *
     * @return mixed|null
     */
    public function __get($name)
    {
        if (isset($this->_data[$name])) {
            return $this->_data[$name];
        } else {
            if ($this->_updates->$name) {
                return $this->_updates->$name;
            }
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): ' . $name . ' in ' . $trace[0]['file'] . ' on line ' . $trace[0]['line'],
            E_USER_NOTICE
        );

        return null;
    }

    /**
     * Queues any updates to user properties
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->_updates->$name = $value;
    }

    /**
     * Get all the user fields as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Check if the data is a Collection if an array convert it to a Collection
     *
     * @param $data
     *
     * @return Collection
     */
    protected function toCollection($data)
    {
        if (is_array($data)) {
            return new Collection($data);
        } else {
            if (!($data instanceof Collection)) {
                // Invalid input type, return empty collection
                $data = new Collection();
            }
        }
        return $data;
    }
}
