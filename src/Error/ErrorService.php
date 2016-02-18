<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 2/16/2016
 * Time: 10:08 PM *
 */

namespace ptejada\uFlex\Error;

use ptejada\uFlex\AbstractSingleton;

/**
 * Class ErrorService
 * @method static ErrorService getInstance()
 *
 * @package ptejada\uFlex\Error
 */
class ErrorService extends AbstractSingleton
{
    public function __construct(){
        // Initializes the first/default error provider
        $this->providers[] = new ErrorProvider();
    }

    /** @var ErrorProvider[] */
    protected $providers = array();

    public function getErrorMessage($errorID)
    {
        foreach($this->providers as $provider){

            if ($message = $provider->getErrorMessage($errorID)) {
                return $message;
            }
        }

        // TODO: Throw an exception
        return null;
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
}
