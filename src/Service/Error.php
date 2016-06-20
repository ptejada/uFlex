<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 2/16/2016
 * Time: 10:08 PM *
 */

namespace ptejada\uFlex\Service;

use ptejada\uFlex\AbstractSingleton;
use ptejada\uFlex\Classes\ErrorProvider;
use ptejada\uFlex\Exception\InternalException;

/**
 * Class ErrorService
 * @method static Error getInstance()

 *
*@package ptejada\uFlex\Error
 */
class Error extends AbstractSingleton
{
    /** @var ErrorProvider[] */
    protected $providers = array();

    public function __construct(){
        // Initializes the first/default error provider
        $this->providers[] = new ErrorProvider();
    }

    public function getErrorMessage($errorID)
    {
        foreach($this->providers as $provider){

            if ($message = $provider->getErrorMessage($errorID)) {
                return $message;
            }
        }

        throw new InternalException("Unknown error[{$errorID}] without message");
    }

    /**
     * Register a new error provider
     * @param ErrorProvider $provider
     */
    public function addProvider(ErrorProvider $provider)
    {
        /*
         * Adds the new provider to the beginning of array so it has priority over existing error providers
         */
        array_unshift($this->providers, $provider);
    }

    /**
     * Dynamically registers a new error message
     * 
     * @param int $errorCode The unique error code
     * @param String $errorMessage The error message
     */
    public function registerError($errorCode, $errorMessage)
    {
        // Register an error in the most recent error provider
        $this->providers[0]->registerError($errorCode, $errorMessage);
    }
}
