<?php

namespace ptejada\uFlex\Classes;

/**
 * An object oriented representation an associative array
 *
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class Collection implements \Iterator, \ArrayAccess
{
    /** @var array The underlying original array */
    protected $_data;
    protected $_autoEscape = false;
    protected $_separator = '.';

    /**
     * Copies an arrays to handle it as an object
     *
     * @param array $info
     * @param bool  $autoEscape
     */
    public function __construct(array $info = array(), $autoEscape=false)
    {
        $this->_data = $info;
        $this->_autoEscape = $autoEscape;
    }

    /**
     * Update the collection with a given array of updates
     *
     * Optionally can provide a path to the array within the collection to update
     * if it path does not exists it will be created
     *
     * @param       $pathOrUpdates
     * @param array $updates
     */
    public function update($pathOrUpdates, array $updates = array())
    {
        if ( is_string($pathOrUpdates) )
        {
            $target = $this->get($pathOrUpdates);
            if ( is_null($target) )
            {
                $this->set($pathOrUpdates, $updates);
            }
            else
            {
                if ( !($target instanceOf Collection) )
                {
                    /*
                     * If the target is not array make it an array
                     */
                    $this->set($pathOrUpdates, array());
                    $target = $this->get($pathOrUpdates);
                }

                /*
                 * Update the target with the given updates
                 */
                foreach($updates as $name => $value)
                {
                    $target->set($name, $value);
                }
            }
        }
        else
        {
            if ( is_array($pathOrUpdates) )
            {
                $this->_data = array_merge($this->_data, $pathOrUpdates);
            }
        }
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
     *
     * NOTE: NULL values will be removed.
     */
    public function filter()
    {
        $this->_data = array_filter(array_intersect_key($this->toArray(), array_flip(func_get_args())),function($value){
            return ! is_null($value);
        });
    }

    /**
     * Flattens an array into a singe dimensional array
     *
     * @example
     *      From: [one => [two => [three=>123]], [four => [five => [six => 456]]]]
     *      To: [one.two.three => 123, four.five.six => 456]
     *
     * @param string $glue - Character used to join nested arrays
     *
     * @return array
     */
    public function flatten($glue = '.')
    {
        $flatArray = array();
        foreach($this as $name => $nameValue){
            $flatArray = array_merge($flatArray, self::getFlat($name, $nameValue, $glue));
        }

        return $flatArray;
    }

    /**
     * Utility function required for self::flatten()
     *
     * @param $name
     * @param $nameValue
     * @param $glue
     *
     * @return array
     */
    protected function getFlat($name, $nameValue, $glue){
        $all = array();
        if ($nameValue instanceof Collection) {
            if ($nameValue->count()) {
                $all = array();
                foreach($nameValue as $subName => $subNameValue)
                {
                    $subAll = self::getFlat("{$name}{$glue}{$subName}", $subNameValue, $glue);
                    $all = array_merge($all, $subAll);
                }
            } else {
                // if collection is empty return empty array
                $all[$name] = array();
            }

        } else {
            $all[$name] = $nameValue;
        }

        return $all;
    }

    /**
     * Expands a single dimension array into a multidimensional traversable object
     * Note: Only keys that include the separator will in be included in the collection.
     *
     * @example
     *      From: [one.two.three => 123, four.five.six => 456]
     *      To: [one => [two => [three=>123]], [four => [five => [six => 456]]]]
     *
     * @param array  $list
     * @param string $separator
     */
    public function expand(array $list, $separator = '.')
    {
        $originalSeparator = $this->_separator;
        $this->_separator = $separator;

        foreach($list as $name => $value){
            if (strpos($name, $separator)) {
                $this->set($name, $value);
            }
        }

        // Restore separator
        $this->_separator = $originalSeparator;
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
        $stops = explode($this->_separator, $keyPath);

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
        $stops = explode($this->_separator, $keyPath);

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
     * Deletes a path from the collection
     * @param $keyPath
     */
    public function delete($keyPath)
    {
        $stops = explode($this->_separator, $keyPath);
        $finalKey = array_pop($stops);
        $pathToFinalKey = implode($this->_separator,$stops);

        if ($pathToFinalKey) {
            $parent = $this->get($pathToFinalKey);
        } else {
            $parent = $this;
        }

        if ($parent instanceof self) {
            unset($parent->$finalKey);
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
                $link = new LinkedCollection($value, $this->_autoEscape);
                $link->setSeparator($this->_separator);
                return $link;
            } else if (is_string($value)) {
                return $this->_autoEscape ?  htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') : $value;
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
        $this->_data[$name] = $value instanceof Collection ? $value->toArray() : $value;
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

    /**
     * @param boolean $autoScape
     */
    public function setAutoEscape($autoScape)
    {
        $this->_autoEscape = (bool) $autoScape;
    }

    /**
     * Return the current element
     *
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $offset = $this->key();
        return $this->$offset;
    }

    /**
     * Move forward to next element
     *
     * @link http://php.net/manual/en/iterator.next.php
     *
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        next($this->_data);
    }

    /**
     * Return the key of the current element
     *
     * @link http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return key($this->_data);
    }

    /**
     * Checks if current position is valid
     *
     * @link http://php.net/manual/en/iterator.valid.php
     *
     * @return boolean The return value will be casted to boolean and then evaluated.
     *       Returns true on success or false on failure.
     */
    public function valid()
    {
        return array_key_exists(key($this->_data),$this->_data);
    }

    /**
     * Rewind the Iterator to the first element
     *
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        reset($this->_data);
    }

    /**
     * Get the count of elements
     * @return int
     */
    public function count()
    {
        return sizeof($this->_data);
    }

    /**
     * Converts into a JSON string
     *
     * @return string
     */
    public function toJsonString()
    {
        return json_encode($this->toArray());
    }

    /**
     * Sets the path separator
     *
     * @param string $separator
     *
     * @throws \Exception
     */
    public function setSeparator($separator)
    {
        if (strlen($separator) == 1) {
            $this->_separator = $separator;
        } else {
            throw new \Exception('A separator must one character');
        }
    }

    /**
     * Whether a offset exists
     *
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset - An offset to check for.
     *
     * @return boolean - true on success or false on failure.
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }



    /**
     * Offset to retrieve
     *
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset - The offset to retrieve.
     *
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Offset to set
     *
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset - The offset to assign the value to.
     * @param mixed $value - The value to set.
     *
     * @return void
     */
    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset - The offset to unset.
     *
     * @return void
     */
    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }
}
