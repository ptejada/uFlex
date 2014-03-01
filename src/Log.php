<?php

namespace Ptejada\UFlex;

class Log
{
    /** @var array - Predefined list of errors, useful for locale messages */
    protected $errorList = array();
    /** @var string - Stores the Log instance internal namespace fro logs */
    protected $namespace = 'Log';
    /** @var string - Stores the current selected channel */
    protected $currentChannel = 'Main';

    protected $console = array(
        'errors'  => array(),
        'reports' => array(),
        'form'    => array(),
    );

    /**
     * Initializes a new log instance with the options to set an initial namespace
     * @param string $namespace
     */
    public function __construct($namespace = null)
    {
        if ($namespace) {
            // Sets the name
            $this->changeNamespace($namespace);
        }
    }

    /**
     * Change the current namespace
     *
     * @param $namespace
     *
     * @return $this
     */
    public function changeNamespace($namespace)
    {
        $this->namespace = $namespace;
        // Changes current channel
        $this->channel('_');
        return $this;
    }

    /**
     * Checks the current channel for errors
     *
     * @return bool
     */
    public function hasError()
    {
        $errors = $this->getErrors();

        return count($errors) > 0;
    }

    /**
     * Get all errors in a specified channel
     *
     * @param string $channel - Channel identifier
     *
     * @return array - returns the error stack by reference, even if empty
     */
    public function &getErrors($channel = null)
    {
        // Uses the passed channel or fallback to the current selected channel
        $channel = $this->namespaceChannel($channel);

        if ( ! is_array($this->console['errors'][$channel]) ) {
            $this->console['errors'][$channel] = array();
        }

        return $this->console['errors'][$channel];
    }

    /**
     * Get all logged errors per channel
     *
     * @return array
     */
    public function getAllErrors()
    {
        return $this->console['errors'];
    }

    /**
     * Get all the report for the current channel or an specific channel
     *
     * @param string [$channel] - an optional channel name
     *
     * @return array
     */
    public function &getReports($channel = null)
    {
        // Uses the passed channel or fallback to the current selected channel
        $channel = $this->namespaceChannel($channel);

        if (!isset($this->console['reports'][$channel])) {
            // Create a new empty array to return as reference
            $this->console['reports'][$channel] = array();
        }

        return $this->console['reports'][$channel];
    }

    /**
     * Get all logged errors per channel
     *
     * @return array
     */
    public function getAllReports()
    {
        return $this->console['reports'];
    }

    /**
     * Log an error to a form field error
     * Note: Only one error per field in a channel namespace
     *
     * @param string $field   - The form field name
     * @param string $message - The error message to link to the field
     *
     * @return $this
     */
    public function formError($field, $message = '')
    {
        $formErrors = &$this->getFormErrors();
        if ($message) {
            $formErrors[$field] = $message;
            $this->error($message);
        } else {
            // if the message if omitted use the field as a generic message
            $formErrors[$field] = $message;
            $this->error($field);
        }

        return $this;
    }

    /**
     * Log an error
     *
     * @param string|int $message - An error message to log or the index of a predefined error
     *
     * @return $this
     */
    public function error($message)
    {
        if ($message) {
            if (is_int($message) && isset($this->errorList[$message])) {
                /*
                 * If the message is of type integer use a predefine
                 * error message
                 */
                $errorMessage = $this->errorList[$message];
                $this->report("Error[{$message}]: {$errorMessage}"); //Report The error
            } else {
                $errorMessage = $message;
                $this->report("Error: {$errorMessage}"); //Report The error
            }

            $errors = &$this->getErrors();
            $errors[] = $errorMessage;
        }

        return $this;
    }

    /**
     * Logs a process report
     *
     * @param $message
     *
     * @return $this
     */
    public function report($message)
    {
        $channel = $this->currentChannel;
        if ($message) {
            // Log the report to the console
            $reports = &$this->getReports($channel);
            $reports[] = $message;
        }

        return $this;
    }

    /**
     * Get form errors by channel
     *
     * @param  string [$channel] - Channel to look for form errors in, if omitted the current channel is used
     *
     * @return array
     */
    public function &getFormErrors($channel = '')
    {
        // Uses the passed channel or fallback to the current selected channel
        $channel = $this->namespaceChannel($channel);

        if (!isset($this->console['form'][$channel])) {
            $this->console['form'][$channel] = array();
        }

        return $this->console['form'][$channel];
    }

    /**
     * Get all the form errors in the console groups by nameSpaced channels
     *
     * @return array
     */
    public function getAllFormErrors()
    {
        return $this->console['form'];
    }

    /**
     * Updates the predefined list of errors
     * Note: The method self::error() which uses the predefined error list only support numeric indexes
     * @param array $errors - Array of error messages
     */
    public function updateErrorList(array $errors)
    {
        $this->errorList = $errors + $this->errorList;
    }

    /**
     * Change the current channel
     *
     * @param $channelName
     *
     * @return $this
     */
    public function channel($channelName)
    {
        $this->currentChannel = $this->namespaceChannel($channelName);
        return $this;
    }

    /**
     * Get the current namespace
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * Converts a plain channel name into a namespace channel identifier
     *
     * @param $channelName
     *
     * @return string
     */
    private function namespaceChannel($channelName)
    {
        if ($channelName) {
            if (strpos($channelName, '::')) {
                // If the channelName seems to be nameSpaced don't change it
                return $channelName;
            } else {
                // Prefixes the namespace to the channelName
                return "{$this->namespace}::{$channelName}";
            }
        } else {
            // Invalid channel name, return the current channel instead
            return $this->currentChannel;
        }
    }
}
