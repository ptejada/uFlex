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
use ptejada\uFlex\Service\Log;
use ptejada\uFlex\Service\Session;

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
     * Get the logging class
     * @return Log
     */
    public static function getLog()
    {
        return Registry::getInstance()->service(Registry::SERVICE_LOG);
    }

    /**
     * Get the session handler
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
}
