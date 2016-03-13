<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/6/2016
 * Time: 12:22 AM
 */

namespace ptejada\uFlex\Service;

use ptejada\uFlex\User;


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
     *
     * @param string $password A clear text password
     * @param string $hash     A hash as returned by
     * @param User   $user
     *
     * @return bool
     */
    public function verifyPassword($password, $hash, User $user = null)
    {
        return password_verify($password, $hash);
    }
}
