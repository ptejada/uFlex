<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/6/2016
 * Time: 12:22 AM
 */

namespace ptejada\uFlex\Service;


/**
 * Class Authenticator handles user password authentication
 *
 * @package ptejada\uFlex\Service
 */
class Authenticator
{
    /**
     * Hash a clear text password
     *
     * @param string $password A clear text password
     *
     * @return bool|false|string The hashed password, false on error
     */
    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /**
     * Verifies that a password matches a hash
     * @param string $password A clear text password
     * @param string $hash A hash as returned by
     *
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }
}
