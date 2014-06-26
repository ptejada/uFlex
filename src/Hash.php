<?php

namespace ptejada\uFlex;

/**
 * Class Hash
 *
 * @package ptejada\uFlex
 * @author  Pablo Tejada <pablo@ptejada.com>
 */
class Hash
{
    /** @var  Log - Log errors and report */
    public $log;

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
     * Initializes the hash object
     */
    public function __construct()
    {
        $this->log = new Log();
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

    /**
     * Generates a unique hash
     *
     * @param int         $uid  user id
     * @param bool|string $hash optional hash to implement
     *
     * @return string
     */
    static public function generate($uid = 0, $hash = false)
    {
        if ($uid) {
            $e_uid = self::encode($uid);
            $e_uid_length = strlen($e_uid);
            $e_uid_length = str_pad($e_uid_length, 2, 0, STR_PAD_LEFT);
            $e_uid_pos = rand(10, 32 - $e_uid_length - 1);

            if (!$hash) {
                $hash = sha1(uniqid(rand(), true));
            }

            $code = $e_uid_pos . $e_uid_length;
            $code .= substr($hash, 0, $e_uid_pos - strlen($code));
            $code .= $e_uid;
            $code .= substr($hash, strlen($code));

            return $code;
        } else {
            return sha1(uniqid(rand(), true));
        }
    }

    /**
     * Checks and validates a confirmation hash
     *
     * @param string $hash hashed string to check
     *
     * @return array
     */
    static public function examine($hash)
    {
        if (strlen($hash) == 40 && preg_match("/^[0-9]{4}/", $hash)) {

            $e_uid_pos = substr($hash, 0, 2);
            $e_uid_length = substr($hash, 2, 2);
            $e_uid = substr($hash, $e_uid_pos, $e_uid_length);

            $uid = self::decode($e_uid);

            preg_match('/^([0-9]{4})(.{2,' . ($e_uid_pos - 4) . '})(' . $e_uid . ')/', $hash, $excerpt);
            $partial = $excerpt[2];

            return array($uid, $partial);
        }
        else
        {
            /*
             * The hash is not valid
             */
            return array(false, false);
        }
    }

    /**
     * Decodes a string into an integer
     *
     * @param string $number string to decode into an integer
     *
     * @return int
     */
    static public function decode($number)
    {
        $k = self::$encoder;
        preg_match_all('/[1][a-zA-Z]|[2-9]|[a-zA-Z]|[0]/', $number, $a);
        $n = '';
        $o = count($k);
        foreach ($a[0] as $i) {
            $f = preg_match('/1([a-zA-Z])/', $i, $v);
            if ($f == true) {
                $i = $o + array_search($v[1], $k);
            } else {
                $i = array_search($i, $k);
            }
            $n .= $i;
        }
        return $n;
    }
}
