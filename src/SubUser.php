<?php

namespace ptejada\uFlex;

use ptejada\uFlex\Classes\Collection;

/**
 * Class UserBase
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class SubUser extends AbstractUser
{
    /** @var array - The user information object */
    protected $_data;
    /** @var Collection - Updates for the user information object */
    protected $_updates;

    /**
     * Initializes the the User object
     *
     * @param array $userData
     */
    public function __construct(array $userData = array())
    {
        // Hydrate the model with user information
        $this->_data = $userData;

        // Initializes the updates collection
        $this->_updates = new Collection();
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
}
