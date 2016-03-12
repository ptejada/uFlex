<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 2/17/2016
 * Time: 9:20 PM
 */

namespace ptejada\uFlex\Exception;



class ErrorException extends UFlexException
{
    const ERROR_REGISTRATION_FAILURE         = 1;
    const ERROR_UPDATE_FAILURE               = 2;
    const ERROR_ACTIVATION_FAILURE           = 3;
    const ERROR_UNKNOWN_EMAIL                = 4;
    const ERROR_INVALID_PASSWORD_RESET_TOKEN = 5;
    const ERROR_COOKIE_LOGIN_FAILURE         = 6;
    const ERROR_MISSING_LOGIN_CREDENTIALS    = 7;
    const ERROR_NOT_YET_ACTIVATED            = 8;
    const ERROR_NOT_ACTIVATED                = 9;
    const ERROR_INVALID_CREDENTIALS          = 10;
    const ERROR_INVALID_CONFIRMATION_TOKEN   = 11;
    const ERROR_EXPIRED_CONFIRMATION_TOKEN   = 12;
    const ERROR_CONFIRMATION_TOKEN_NOT_SAVED = 13;
    const ERROR_PENDING_PASSWORD_RESET       = 14;
    const ERROR_DOUBLE_REGISTRATION_FAILURE  = 15;
    const ERROR_EMAIL_IN_USE                 = 16;
    const ERROR_USERNAME_IN_USE              = 17;

    public function __construct($code, \Exception $previous = null)
    {
        $message = ErrorService::getInstance()->getErrorMessage($code);

        if ($previous) {
            parent::__construct($message, $code, $previous);
        } else {
            parent::__construct($message, $code);
        }
    }
}
