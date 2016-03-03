<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/2/2016
 * Time: 9:21 PM
 */

namespace ptejada\uFlex\Classes;

/**
 * Class Utility includes function meant to be used across classes *
 * All public methods must be declared statically
 *
 * @package ptejada\uFlex
 */
class Utility
{
    protected function __construct()
    {
        //
    }

    /**
     * Convert an array to a Collection object
     *
     * @param mixed $info Input to covert to Collection object
     *
     * @return Collection
     * @throws \Exception If can not convert $info to a Collection
     */
    public static function getCollection($info)
    {
        if (is_array($info)) {
            return new Collection($info);
        } else {
            if ($info instanceof Collection) {
                return $info;
            } else {
                $type = gettype($info);
                $type = $type == 'object' ? get_class($info) : $type;
                throw new \Exception("Unable to convert resource of type '{$type}'.");
            }
        }
    }
}
