<?php

namespace Ptejada\UFlex;

class Collection
{
    /** @var array */
    protected $_data;

    public function __construct(array $info = array())
    {
       $this->_data = $info;
    }

    /**
     * Update the collection with a given array of updates
     * @param array $updates
     */
    public function update(array $updates)
    {
        $this->_data = array_merge($this->_data, $updates);
    }

    /**
     * Return the raw underling array of the collection
     * @return array
     */
    public function &toArray()
    {
        return $this->_data;
    }

    public function __set($name, $value)
    {
        $this->_data[$name] = $value;
    }

    /**
     * @param $name
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

    public function __isset($name)
    {
        return isset($this->_data[$name]);
    }

    public function __unset($name)
    {
        unset($this->_data[$name]);
    }
}
