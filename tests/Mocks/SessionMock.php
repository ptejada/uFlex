<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 6/19/2016
 * Time: 1:58 PM
 */

namespace tests\Mocks;


use ptejada\uFlex\Service\Session;

class SessionMock extends Session
{
    protected $sid;

    public function __construct($namespace = null, $lifespan = 0){
        $this->sid = uniqid();
        
        parent::__construct($namespace, $lifespan);
    }

    public function getID()
    {
        return $this->sid;
    }

}
