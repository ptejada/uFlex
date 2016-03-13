<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/12/2016
 * Time: 1:36 AM
 */

namespace ptejada\uFlex\Service;


use ptejada\uFlex\Classes\Table;
use ptejada\uFlex\Config;
use ptejada\uFlex\SubUser;

class UserManager
{
    /** @var  Table */
    protected $table;

    public function __construct()
    {
        $this->table = Config::getConnection()->getTable(Config::get('user.table'));
    }

    /**
     * Get a user by its ID
     *
     * @param int $id The user ID
     *
     * @return null|SubUser
     */
    public function getUser($id)
    {
        $row = $this->table->getRow(array('ID' => $id));
        if (!$row->isEmpty()) {
            return new SubUser($row->toArray());
        } else {
            return null;
        }
    }
}
