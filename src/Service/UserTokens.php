<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 9/12/2016
 * Time: 8:47 PM
 */

namespace ptejada\uFlex\Service;


use ptejada\uFlex\Classes\Collection;
use ptejada\uFlex\Config;

class UserTokens
{
    const TYPE_AUTO_LOGIN      = 1;
    const TYPE_PASSWORD_RESET  = 2;
    const TYPE_PASSWORD_CHANGE = 3;

    public function __construct()
    {
        $this->table = Config::getConnection()->getTable('UserTokens');
    }

    /**
     * Get the most recent generated token
     *
     * @param $uid
     * @param $tokenType
     *
     * @return bool|Collection
     */
    public function getToken($uid, $tokenType)
    {
        $stmt = $this->table->getStatement('
            SELECT * FROM UserTokens WHERE UID = :uid AND Type = :tType
            ORDER BY CreateTime ASC
            LIMIT 1
        ', array('UID' => $uid, 'TokenType', $tokenType));

        $stmt->execute();
        return $stmt->fetch();
//        return $this->table->getRow(array('UID' => $uid, 'TokenType', $tokenType));
    }

    /**
     * @param $token
     * @param $tokenType
     *
     * @return Collection
     */
    public function validate($token, $tokenType)
    {
        $record = $this->getToken($token, $tokenType);
        if ($record) {
            $expTime = new \DateTime($record->ExpirationTime);
            $diff    = $expTime->diff(new \DateTime());

            $record->filter('UID', 'Type', 'CreateTime');
            $record->valid = (bool)$diff->s;

            return $record;
        }

        return new Collection();
    }

    /**
     * Generate a unique token for a user
     *
     * @param        $uid
     * @param        $tokenType
     * @param string $lifespan A valid string to time modifier
     *
     * @return string
     */
    public function generate($uid, $tokenType, $lifespan = '24 hours')
    {
        $expTime = new \DateTime();
        $createTime = $expTime->format('Y-m-d H:i:s');
        $expTime->modify('+ ' . trim($lifespan, '-+'));
        $stmt = $this->table->getStatement(
            'INSERT INTO UserTokens(UID, Token, Type, CreateTime, ExpirationTime) VALUES(:uid, :token, :tType, :cTime, :expTime)'
        );

        $stmt->bindValue(':uid', $uid, \PDO::PARAM_INT);
        $stmt->bindValue(':tType', $tokenType, \PDO::PARAM_INT);
        $stmt->bindValue(':expTime', $expTime->format('Y-m-d H:i:s'), \PDO::PARAM_STR);
        $stmt->bindValue(':cTime', $createTime, \PDO::PARAM_STR);

        do {
            // TODO: Limit the the amount of tries
            $uniqueToken = $this->newToken();
            $stmt->bindValue(':token', $uniqueToken, \PDO::PARAM_STR);
            $stmt->execute();

            $tokenID = $this->table->getLastInsertedID();
        } while(!$tokenID);

        return $uniqueToken;
    }

    /**
     * Generated random token
     * @return string
     */
    protected function newToken()
    {
        return sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0xffff ),
            mt_rand( 0, 0x0C2f ) | 0x4000,
            mt_rand( 0, 0x3fff ) | 0x8000,
            mt_rand( 0, 0x2Aff ),
            mt_rand( 0, 0xffD3 ),
            mt_rand( 0, 0xff4B )
        );
    }
}
