<?php

namespace Ptejada\UFlex;

/**
 * Class to handle the PHP session
 * The entire PHP session can be handled
 * Or a direct member of the main session array
 * which is refer as a namespace
 *
 * @package Ptejada\UFlex
 */
class Session extends Collection
{
    /** @var  Log - Log errors and report */
    public $log;

    /**
     * Initialize a session handler by namespace
     *
     * @param string $namespace - Session namespace to manage
     */
    public function __construct($namespace = null)
    {
        $this->log = new Log('Session');

        // Starts the session if it has not been started yet
        if (!isset($_SESSION) && !headers_sent()) {
            session_start();
            $this->log->report("Session is been started...");
        } elseif (isset($_SESSION)) {
            $this->log->report("Session has already been started");
        } else {
            $this->log->error("Session could not be started");
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
}
