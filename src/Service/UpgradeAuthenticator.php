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
class UpgradeAuthenticator extends Authenticator
{
    /**
     * Required for the integer encoder and decoder functions
     *
     * @var array
     * @access protected
     * @ignore
     */
    static protected $encoder = array(
        // @formatter:off
        'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
        'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
        0,2,3,4,5,6,7,8,9
        // @formatter:on
    );

    /**
     * Verifies that a password matches a hash
     * @param string $password A clear text password
     * @param string $hash A hash as returned by
     *
     * @return bool
     */
    public function verifyPassword($password, $hash)
    {
        // TODO: Detect old password and upgrade
        return password_verify($password, $hash);
    }

    /**
     * Generate a password for a user
     *
     * @param User   $user
     * @param String $password - Clear text password
     * @param bool   $generateOld
     *
     * @return string
     */
    public function generateUserPassword(User $user, $password, $generateOld = false)
    {
        $registrationDate = $user->RegDate;

        $pre = $this->encode($registrationDate);
        $pos = substr($registrationDate, 5, 1);
        $post = $this->encode($registrationDate * (substr($registrationDate, $pos, 1)));

        $finalString = $pre . $password . $post;

        return $generateOld ? md5($finalString) : sha1($finalString);
    }

    /**
     * Encodes an integer
     *
     * @param int $number integer to encode
     *
     * @return string encoded integer string
     */
    static protected function encode($number)
    {
        $k = self::$encoder;
        preg_match_all("/[1-9][0-9]|[0-9]/", $number, $a);
        $n = '';
        $o = count($k);
        foreach ($a[0] as $i) {
            if ($i < $o) {
                $n .= $k[$i];
            } else {
                $n .= '1' . $k[$i - $o];
            }
        }
        return $n;
    }
}
