<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 5:33 PM
 */

namespace tests;


use Ptejada\UFlex\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase {


    public function testGetters()
    {
        $info = array(
            'string' => 'Hello World',
            'zero' => 0,
            'empty' => '',
            'username' => array(
                'limit' => '3-15',
                'regEx' => '/^([a-zA-Z0-9_])+$/'
            ),
            'password' => array(
                'limit' => '3-15',
                'regEx' => ''
            ),
            'email'    => array(
                'limit' => '4-45',
                'regEx' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i'
            )
        );

        $list = new Collection($info);

        $this->assertEquals($info['string'], $list->string, 'Simple single item getter from collection');
        $this->assertEquals($info['password']['limit'], $list->password->limit, 'Chained Collection getters');

        $this->assertNull($list->shouldBeNull);
        $this->assertNotNull($list->zero);
        $this->assertNotNull($list->empty);

        $this->assertEquals($info, $list->getAll(), 'Compares the full collection with the raw original array');
    }

    public function testSetters()
    {
        $list = new Collection();

        $list->locations = array();
        $list->locations->one = 1;
        $list->locations->two = 2;

        $expectation = array(
            'locations' => array(
                'one' => 1,
                'two' => 2,
            )
        );

        $this->assertEquals($expectation, $list->getAll());
    }


}
 