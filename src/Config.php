<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/2/2016
 * Time: 9:01 PM
 */

namespace ptejada\uFlex;

use ptejada\uFlex\Classes\Registry;
use ptejada\uFlex\Service\Authenticator;
use ptejada\uFlex\Service\Connection;
use ptejada\uFlex\Service\Log;
use ptejada\uFlex\Service\Session;
use ptejada\uFlex\Service\Validation\Validator;

/**
 * Class Config to configure all settings
 *
 * @package ptejada\uFlex
 */
class Config
{
    private function __construct()
    {
        //
    }

    /**
     * Get a configuration option
     *
     * @param string $name Configuration option
     *
     * @return mixed|null|Classes\Collection
     */
    public static function get($name)
    {
        return Registry::getInstance()->option($name);
    }

    /**
     * Set a configuration option
     *
     * @param string $name Name of the config option
     * @param mixed  $value New config option value
     */
    public static function set($name, $value)
    {
        Registry::getInstance()->setOption($name, $value);
    }

    /**
     * Get the DB connection class
     *
     * @return Connection
     */
    public static function getConnection()
    {
        return Registry::getInstance()->service(Registry::SERVICE_CONNECTION);
    }

    /**
     * Get the logging class
     *
     * @return Log
     */
    public static function getLog()
    {
        return Registry::getInstance()->service(Registry::SERVICE_LOG);
    }

    /**
     * Get the session handler
     *
     * @return Session
     */
    public static function getSession()
    {
        return Registry::getInstance()->service(Registry::SERVICE_SESSION);
    }

    /**
     * Get the authenticator handler
     *
     * @return Authenticator
     */
    public static function getAuth()
    {
        return Registry::getInstance()->service(Registry::SERVICE_AUTH);
    }

    /**
     * Get the input validation handler
     *
     * @return Validator
     */
    public static function getValidator()
    {
        return Registry::getInstance()->service(Registry::SERVICE_VALIDATOR);
    }

    /**
     * Register a new class to handle the DB connection
     *
     * @param string $connectionClass New DB connection class
     *
     * @throws \Exception
     */
    public static function registerConnection($connectionClass)
    {
        Registry::getInstance()->registerService(Registry::SERVICE_CONNECTION, $connectionClass);
    }

    /**
     * Register a new class to handle logging
     *
     * @param string $logClass New Log class
     *
     * @throws \Exception
     */
    public static function registerLog($logClass)
    {
        Registry::getInstance()->registerService(Registry::SERVICE_SESSION, $logClass);
    }

    /**
     * Register a new class to handle the session
     *
     * @param string $sessionClass New session class
     *
     * @throws \Exception
     */
    public static function registerSession($sessionClass)
    {
        Registry::getInstance()->registerService(Registry::SERVICE_SESSION, $sessionClass);
    }

    /**
     * Register a new class to handle authentication
     *
     * @param string $authClass New authentication class
     *
     * @throws \Exception
     */
    public static function registerAuth($authClass)
    {
        Registry::getInstance()->registerService(Registry::SERVICE_AUTH, $authClass);
    }

    /**
     * Register a new class to handle input validation
     *
     * @param string $validatorClass New validation class
     *
     * @throws \Exception
     */
    public static function registerValidator($validatorClass)
    {
        Registry::getInstance()->registerService(Registry::SERVICE_VALIDATOR, $validatorClass);
    }
}
