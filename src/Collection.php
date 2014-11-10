<?php

namespace ptejada\uFlex;

/**
 * An object oriented representation an associative array
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class Collection
{
    /** @var array The underlying original array */
    protected $_data;

    /**
     * Copies an arrays to handle it as an object
     * @param array $info
     */
    public function __construct(array $info = array())
    {
        $this->_data = $info;
    }

    /**
     * Update the collection with a given array of updates
     *
     * @param array $updates
     */
    public function update(array $updates)
    {
        $this->_data = array_merge($this->_data, $updates);
    }

    /**
     * Return the raw underling array of the collection
     *
     * @return array
     */
    public function &toArray()
    {
        return $this->_data;
    }

    /**
     * Checks if the collection is empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_data);
    }

    /**
     * Reduces the collection to only include allowed fields
     * Every argument passed to this function is considered a field
     */
    public function filter()
    {
        $this->_data = array_intersect_key($this->toArray(), array_flip(func_get_args()));
    }

    /**
     * Get a value of an entry in the collection
     * Useful to get deep array elements without manually dealing with errors
     * During the process
     *
     * @example Consider the below examples:
     *
     *      // If the 'two' is not defined this code will trigger a PHP notice
     *      $list->one->two->three->four->five
     *      // This method will never trigger a PHP notice, safe to use at any depth
     *      $list->get('one.two.three.four.five')
     *
     * @param string $keyPath - the period delimited location
     *
     * @return mixed|null|Collection
     */
    public function get($keyPath)
    {
        $stops = explode('.', $keyPath);

        $value = $this;
        foreach ($stops as $key) {
            if ($value instanceof Collection) {
                // Move one step deeper into the collection
                $value = $value->$key;
            } else {
                /*
                 * One more stops still pending and the current
                 * value is not a collection, terminate iteration
                 * and set value to null
                 */
                $value = null;
                break;
            }
        }

        return $value;
    }

    /**
     * Set a value to an index in the collection
     * Used when the collection are nested
     *
     * @param string $keyPath
     * @param string $value
     */
    public function set($keyPath, $value)
    {
        $stops = explode('.', $keyPath);

        $currentLocation = $previousLocation = $this;
        foreach ($stops as $key) {
            if ($currentLocation instanceof Collection) {
                // Move one step deeper into the collection
                if (!($currentLocation->$key instanceof Collection)) {
                    $currentLocation->$key = array();
                }
            } else {
                $currentLocation = array();
                $currentLocation->$key = array();
            }
            $previousLocation = $currentLocation;
            $currentLocation = $currentLocation->$key;
        }

        // Set the value
        $previousLocation->$key = $value;
    }

    /**
     * Return the number of items in the collection
     *
     * @return int
     */
    public function count()
    {
        if (!$this->isEmpty()) {
            return count($this->_data);
        } else {
            return 0;
        }
    }

    /**
     * Magic getter for all first child properties
     *
     * @param string $name
     *
     * @return mixed|LinkedCollection
     */
    public function __get($name)
    {
        if (array_key_exists($name, $this->_data)) {
            $value =& $this->_data[$name];

            if (is_array($value)) {
                return new LinkedCollection($value);
            } else {
                return $value;
            }
        }
        return null;
    }

    /**
     * Magic setter for all first child properties
     *
     * @param string $name
     * @param string $value
     */
    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * Check a property exists
     *
     * @param string $name
     *
     * @return bool
     */
    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    /**
     * Deletes a property from the collection
     *
     * @param string $name
     */
    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
