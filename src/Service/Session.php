<?php

namespace ptejada\uFlex\Service;

use ptejada\uFlex\Classes\Collection;
use ptejada\uFlex\Classes\LinkedCollection;
use ptejada\uFlex\Config;
use ptejada\uFlex\Exception\InternalException;

/**
 * Class to handle the PHP session
 * The entire PHP session can be handled
 * Or a direct member of the main session array
 * which is refer as a namespace
 *
 * @package ptejada\uFlex
 * @author Pablo Tejada <pablo@ptejada.com>
 */
class Session extends LinkedCollection
{
    /**
     * Log errors and report
     * @var  Log
     * @deprecated
     */
    public $log;

    /** @var null|string Session index to manage */
    protected $namespace;
    protected $lifespan = 0;

    /**
     * Initialize a session handler by namespace
     *
     * @param string $namespace - Session namespace to manage
     * @param int    $lifespan How long should the session live for in seconds
     *
     * @throws InternalException
     */
    public function __construct($namespace = null, $lifespan = 0)
    {
        $this->namespace = $namespace;
        $this->lifespan = (int) $lifespan;
        $this->log = Config::getLog();
        $this->init();
    }

    /**
     * Initializes the session.
     * Should validate the session too.
     * @throws \Exception If the session can not be initialized
     */
    protected function init()
    {
        $log = Config::getLog();

        // Starts the session if it has not been started yet
        if ($this->isSessionStarted()) {
            $log->log('Session has already been started');
        } else {
            if (!headers_sent()) {
                session_start();
                $log->log('Session is been started...');
            } else {
                $log->error('Failed to initialize the session');
                throw new InternalException('Failed to start the session because request output has already started');
            }
        }

        if (is_null($this->namespace)) {
            // Manage the whole session
            parent::__construct($_SESSION);
        } else {
            if (!isset($_SESSION[$this->namespace])) {
                // Initialize the session namespace if does not exists yet
                $_SESSION[$this->namespace] = array();
            }

            // Link the SESSION namespace to the local $data variable
            parent::__construct($_SESSION[$this->namespace]);
        }

        $this->validate();
    }

    /**
     * Creates new session namespace
     *
     * @param string $namespace
     * @param int    $lifespan How long should the session live for in seconds
     *
     * @return Session
     */
    public static function newSession($namespace = null, $lifespan = 0)
    {
        return new self($namespace, $lifespan);
    }

    protected function isSessionStarted()
    {
        if ( php_sapi_name() !== 'cli' ) {
            if ( version_compare(phpversion(), '5.4.0', '>=') ) {
                return session_status() === PHP_SESSION_ACTIVE ? true : false;
            } else {
                return $this->getID() == '' ? false : true;
            }
        } else {
            return $this->getID() != '';
        }
    }

    /**
     * Validates the session
     */
    protected function validate()
    {
        /*
         * Get the correct IP
         */
        $server = new Collection($_SERVER);
        $ip = $server->HTTP_X_FORWARDED_FOR;

        if (is_null($ip) && $server->REMOTE_ADDR)
        {
            $ip = $server->REMOTE_ADDR;
        }

        if (!is_null($this->_ip)) {
            if ($this->_ip != $ip) {
                /*
                 * Destroy the session in the IP stored in the session is different
                 * then the IP of the current request
                 */
                $this->destroy();
                $log = Config::getLog()->section('Session');
                $log->error("The session[{$this->namespace}]' was destroyed because of IP mismatch.");
                $log->debug("Current session IP {$this->_ip} did not not matched new IP {$ip}.");
                return false;
            }
        } else {
            /*
             * Save the current request IP in the session
             */
            $this->_ip = $ip;
        }

        /*
         * Only if the session auto expires
         */
        if ($this->lifespan) {
            if (is_numeric($this->_time)) {
                $gap = time() - $this->_time;
                if ($gap > $this->lifespan ) {
                    $this->destroy();
                    $log = Config::getLog()->section('Session');
                    $log->error("The session[{$this->namespace}]' was destroyed because the lifespan expired.");
                    $log->debug("The session was attempted to be restored after {$gap} seconds.");
                    return false;
                }
            }
            // Sets or update the time
            $this->_time = time();
        }

        return true;
    }

    /**
     * Get current session ID identifier
     *
     * @return string
     */
    public function getID()
    {
        return session_id();
    }

    /**
     * Clears the session or namespace
     */
    public function destroy()
    {
        if (is_null($this->namespace)) {
            // Destroy the whole session
            session_destroy();
        } else {
            // Just empty the current session namespace
            $_SESSION[$this->namespace] = array();
            unset($_SESSION[$this->namespace]);
        }
    }
}
