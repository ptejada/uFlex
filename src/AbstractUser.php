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
    /** @var array - The user information object */
    protected $data;
    /** @var Collection - Updates for the user information object */
    protected $dataUpdates;

    /**
     * Initializes the the User object
     *
     * @param array $userData
     */
    public function __construct(array $userData = array())
    {
        // Hydrate the model with user information
        $this->data = $userData;

        // Initializes the dataUpdates collection
        $this->dataUpdates = new Collection();
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
        if (isset($this->data[$name])) {
            return $this->data[$name];
        } else {
            if ($this->dataUpdates->$name) {
                return $this->dataUpdates->$name;
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
     * Queues any dataUpdates to user properties
     *
     * @param $name
     * @param $value
     */
    public function __set($name, $value)
    {
        $this->dataUpdates->$name = $value;
    }

    /**
     * Get all the user fields as an array
     *
     * @return array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * @ignore
     */
    protected function __clone()
    {
        // Prevents user cloning
    }
}
