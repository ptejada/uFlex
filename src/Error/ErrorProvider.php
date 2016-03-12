<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 2/16/2016
 * Time: 10:35 PM
 */

namespace ptejada\uFlex\Error;

use ptejada\uFlex\Exception\ErrorException;

/**
 * Class ErrorProvider
 *
 * @package ptejada\uFlex\Error
 */
class ErrorProvider
{
    protected $errors = array(
        ErrorException::ERROR_REGISTRATION_FAILURE         => 'New User Registration Failed.',
        ErrorException::ERROR_UPDATE_FAILURE               => 'The Changes Could not be made',
        ErrorException::ERROR_ACTIVATION_FAILURE           => 'Account could not be activated',
        ErrorException::ERROR_UNKNOWN_EMAIL                => 'We don\'t have an account with this email',
        ErrorException::ERROR_INVALID_PASSWORD_RESET_TOKEN => 'Password could not be changed. The request can\'t be validated',
        ErrorException::ERROR_COOKIE_LOGIN_FAILURE         => 'Logging in with cookies failed',
        ErrorException::ERROR_MISSING_LOGIN_CREDENTIALS    => 'No Username or Password provided',
        ErrorException::ERROR_NOT_YET_ACTIVATED            => 'Your Account has not been Activated. Check your Email for instructions',
        ErrorException::ERROR_NOT_ACTIVATED                => 'Your account has been deactivated. Please contact Administrator',
        ErrorException::ERROR_INVALID_CREDENTIALS          => 'Wrong Username or Password',
        ErrorException::ERROR_INVALID_CONFIRMATION_TOKEN   => 'Confirmation hash is invalid',
        ErrorException::ERROR_EXPIRED_CONFIRMATION_TOKEN   => 'Your identification could not be confirmed',
        ErrorException::ERROR_CONFIRMATION_TOKEN_NOT_SAVED => 'Failed to save confirmation request',
        ErrorException::ERROR_PENDING_PASSWORD_RESET       => 'You need to reset your password to login',
        ErrorException::ERROR_DOUBLE_REGISTRATION_FAILURE  => 'Can not register a new user, as user is already logged in.',
        ErrorException::ERROR_EMAIL_IN_USE                 => 'This Email is already in use',
        ErrorException::ERROR_USERNAME_IN_USE              => 'This Username is not available',
    );

    /**
     * Get the error message for a code
     * @param $errorCode
     *
     * @return string|null
     */
    public function getErrorMessage($errorCode)
    {
        if (isset($this->errors[$errorCode])) {
            return $this->errors[$errorCode];
        }

        return null;
    }

    /**
     * List of error codes served by the provider
     * @return mixed[]
     */
    public function provides()
    {
        return array_keys($this->errors);
    }

    /**
     * Registers a new error code with a message
     * @param $errorCode
     * @param $errorMessage
     */
    public function registerError($errorCode, $errorMessage)
    {
        $this->errors[$errorCode] = $errorMessage;
    }
}
