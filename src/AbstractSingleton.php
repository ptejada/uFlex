<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 2/16/2016
 * Time: 10:14 PM
 */

namespace ptejada\uFlex;


abstract class AbstractSingleton
{
    /** @var static */
    protected static $instance;

    protected function __construct()
    {
        //
    }

    public static function getInstance()
    {
        if (!(static::$instance instanceof static)) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
