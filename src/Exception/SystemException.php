<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/12/2016
 * Time: 12:55 AM
 */

namespace ptejada\uFlex\Exception;

use Exception;
use ptejada\uFlex\Config;

/**
 * Class SystemException is meant to be use to filter any exception thrown by the library
 *
 * @package ptejada\uFlex\Exception
 */
abstract class SystemException extends \Exception
{
    /** @var String the micro time when exception is initialized  */
    protected $time;
    /** @var  Int The log level enumeration */
    protected $level;
    /** @var  String The log section name */
    protected $section;
    
    public function __construct($message='', $code=0, Exception $previous = null)
    {
        $this->time = microtime();

        if (is_numeric($message)) {
            $code = $message;
            $message = '';
        }

        if (empty($message)) {
            $message = Config::getLog()->errorService->getErrorMessage($code);
        }
        
        parent::__construct(
            (string) $message, $code, $previous
        );
    }

    /**
     * @param Int $level
     *
     * @return SystemException
     */
    public function setLevel($level)
    {
        $this->level = $level;
        return $this;
    }

    /**
     * @return Int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * @param String $section
     *
     * @return SystemException
     */
    public function setSection($section)
    {
        $this->section = $section;
        return $this;
}

    /**
     * @return String
     */
    public function getSection()
    {
        return $this->section;
    }
}
