<?php

namespace ptejada\uFlex;

/**
 * Console to log reports and errors
 *
 * @package ptejada\uFlex
 * @author Pablo Tejada <pablo@ptejada.com>
 */
class Log
{
    /** @var array - Predefined list of errors, useful for locale messages */
    protected $errorList = array();
    /** @var string - Stores the Log instance internal namespace fro logs */
    protected $namespace = 'Log';
    /** @var string - Stores the current selected channel */
    protected $currentChannel = '_main';

    /** @var array The console to store all logs */
    protected $console = array(
        'errors'  => array(),
        'reports' => array(),
        'form'    => array(),
    );

    /**
     * Initializes a new log instance with the options to set an initial namespace
     *
     * @param string $namespace
     * @param Log    $logLink
     */
    public function __construct($namespace = null, Log $logLink=null)
    {
        if ($namespace) {
            // Sets the name
            $this->changeNamespace($namespace);
        }

        if (!is_null($logLink)) {

            // Links the console with an existing instance
            $this->console = &$logLink->getFullConsole();
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
        $this->cleanConsole();

        $this->namespace = $namespace;
        // Changes current channel
        $this->channel('_main');
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
     * Get the full console array
     * @return array
     */
    public function &getFullConsole()
    {
        return $this->console;
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

        if ( ! isset($this->console['errors'][$channel]) ) {
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
     * @param string $channel - (optional) Channel name
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
     * @param string|int $message - The error message to link to the field or an ID of a predefined error message
     *
     * @return $this
     */
    public function formError($field, $message = '')
    {
        $formErrors = &$this->getFormErrors();
        if ($message) {
            if (is_int($message) && isset($this->errorList[$message])) {
                // Use predefined error
                $formErrors[$field] = $this->errorList[$message];
            }else{
                // Use given error message
                $formErrors[$field] = $message;
            }
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
     * @param  string $channel - (optional) Channel to look for form errors in, if omitted the current channel is used
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
     * Note: note changing to a channel with existing errors
     * from previous calls will be cleared
     *
     * @param $channelName
     *
     * @return $this
     */
    public function channel($channelName)
    {
        $this->cleanConsole();
        $this->currentChannel = $this->namespaceChannel($channelName);
        // Mark start of a new start
        $this->report(">> New $channelName request");
        // Clear any errors on the channel
        $this->clearErrors();
        return $this;
    }

    /**
     * Clears existing errors for a channel
     *
     * @param string $channelName
     */
    public function clearErrors($channelName='')
    {
        $channel = $this->namespaceChannel($channelName);

        // Clear any existing errors for the channel
        $this->console['errors'][$channel] = array();
        $this->console['form'][$channel] = array();
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

    /**
     * Creates a new instance of Log with a linked console
     *
     * @param $namespace
     *
     * @return Log
     */
    public function newChildLog($namespace)
    {
        $child = new Log($namespace, $this);
        return $child;
    }

    /**
     * Adds a predefined error to the internal list
     *
     * @param int|array $id Must be numeric or associative array with numeric keys
     * @param string    $message
     */
    public function addPredefinedError($id, $message = '')
    {
        if (is_array($id)) {
            $this->errorList = array_diff_key($this->errorList, $id) + $id;
        } else {
            $this->errorList[$id] = $message;
        }
    }

    /**
     * Removes any empty namespace of the current channel from the console
     */
    private function cleanConsole()
    {
        $channel = $this->namespaceChannel($this->currentChannel);

        if (empty($this->console['errors'][$channel])) {
            unset($this->console['errors'][$channel]);
        }

        if (empty($this->console['form'][$channel])) {
            unset($this->console['form'][$channel]);
        }

        if (empty($this->console['reports'][$channel])) {
            unset($this->console['reports'][$channel]);
        }
    }
}
