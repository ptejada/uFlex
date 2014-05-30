<?php

namespace ptejada\uFlex;

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
    /** @var  Log - Log errors and report */
    public $log;

    /** @var null|string */
    protected $namespace;

    /**
     * Initialize a session handler by namespace
     *
     * @param string $namespace - Session namespace to manage
     * @param   Log  $log
     */
    public function __construct($namespace = null, Log $log = null)
    {
        $this->log = $log instanceof Log ? $log : new Log('Session');
        $this->namespace = $namespace;

        // Starts the session if it has not been started yet
        if (!isset($_SESSION) && !headers_sent()) {
            session_start();
            $this->log->report('Session is been started...');
        } elseif (isset($_SESSION)) {
            $this->log->report('Session has already been started');
        } else {
            $this->log->error('Session could not be started');
        }

        if (is_null($namespace)) {
            // Manage the whole session
            parent::__construct($_SESSION);
        } else {
            if (!isset($_SESSION[$namespace])) {
                // Initialize the session namespace if does not exists yet
                $_SESSION[$namespace] = array();
            }

            // Link the SESSION namespace to the local $data variable
            parent::__construct($_SESSION[$namespace]);
        }
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
     * Empty the session namespace
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
