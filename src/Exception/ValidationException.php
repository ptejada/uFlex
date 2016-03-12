<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/2/2016
 * Time: 8:58 PM
 */

namespace ptejada\uFlex\Exception;

class ValidationException extends UFlexException
{
    /** Error code if input is shorter than expected */
    const ERROR_MINIMUM = 1;
    /** Error code if input is longer than expected */
    const ERROR_MAXIMUM = 2;
    /** Error code if the pattern does not match */
    const ERROR_PATTERN = 3;
    /** Error code the two fields do not match */
    const ERROR_MISMATCH = 4;

    /** @var string Name of the field for the exception */
    protected $fieldName;

    /**
     * ValidationException constructor.
     *
     * @param int            $code The error code
     * @param string         $fieldName The name field with the error
     * @param string         $message The error message
     * @param \Exception|null $previous Any previous exception
     */
    public function __construct($code, $fieldName, $message = null, \Exception $previous = null)
    {
        if (is_null($message)) {
            // Generic message if one is not included
            $message = "[{$fieldName}] Generic error {$code}";
        }

        // Prefix the error message with the field name
        $message = "[{$fieldName}] $message";

        if (is_null($previous)) {
            parent::__construct($message, $code);
        } else {
            parent::__construct($message, $code, $previous);
        }

        $this->fieldName = $fieldName;
    }

    /**
     * Get the exception field name
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }
}
