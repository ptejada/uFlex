<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/23/14
 * Time: 5:33 PM
 */

namespace tests;


use ptejada\uFlex\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase {


    public function testGetters()
    {
        $info = array(
            'string' => 'Hello World',
            'zero' => 0,
            'empty' => '',
            'Username' => array(
                'limit' => '3-15',
                'regEx' => '/^([a-zA-Z0-9_])+$/'
            ),
            'Password' => array(
                'limit' => '3-15',
                'regEx' => ''
            ),
            'Email'    => array(
                'limit' => '4-45',
                'regEx' => '/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/i'
            )
        );

        $list = new Collection($info);

        $this->assertEquals($info['string'], $list->string, 'Simple single item getter from collection');
        $this->assertEquals($info['Password']['limit'], $list->Password->limit, 'Chained Collection getters');

        $this->assertNull($list->shouldBeNull);
        $this->assertNotNull($list->zero);
        $this->assertNotNull($list->empty);

        $this->assertEquals($info, $list->toArray(), 'Compares the full collection with the raw original array');
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

        $this->assertEquals($expectation, $list->toArray());
    }

    public function testExistence()
    {
        $list = new Collection(array(
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
        ));

        $this->assertTrue(isset($list->one));
        $this->assertFalse(isset($list->five));

        $this->assertNull($list->five);
        $this->assertNull($list->get('two.one'));
    }

    public function testUnsetter()
    {
        $list = new Collection(array(
            'one' => array(
                'two' => 2
            ),
            'two' => 2,
            'three' => 3,
            'four' => 4,
        ));

        $this->assertTrue(isset($list->two));
        unset($list->two);
        $this->assertFalse(isset($list->two));
        $list->two = 2;
        $this->assertTrue(isset($list->two));
        $list->two = null;
        $this->assertFalse(isset($list->two));

        $this->assertTrue(isset($list->one->two));
        unset($list->one->two);
        $this->assertFalse(isset($list->one->two));
        $list->one->two = 2;
        $this->assertTrue(isset($list->one->two));
        $list->one->two = null;
        $this->assertFalse(isset($list->one->two));
    }

    public function testStringGetter()
    {
        $list = new Collection(array(
            'one' => array(
                'two' => 2
            ),
            'two' => 2,
            'three' => 3,
            'four' => 4,
        ));

        error_reporting(E_ALL);

        $this->assertEquals(array('two'=>2), $list->get('one')->toArray());
        $this->assertEquals(2, $list->get('one.two'));
        $this->assertEquals(null, $list->get('one.two.three'));


    }

    public function testStringSetter()
    {
        $list = new Collection(array(
            'one' => array(
                'two' => 2
            ),
            'two' => 2,
            'three' => 3,
            'four' => 4,
        ));

        error_reporting(E_ALL);

        $list->set('five',5);
        $this->assertEquals(5, $list->five);

        $list->set('one.two.three',3);
        $this->assertEquals(3, $list->one->two->three);

        $list->set('two.three.four.five', array(1,1,1,1,1));
        $this->assertEquals(array(1,1,1,1,1), $list->two->three->four->five->toArray());
    }

    public function testFilter()
    {
        $list = new Collection(array(
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
        ));

        $this->assertNotNull($list->one);
        $this->assertNotNull($list->four);

        $list->filter('two', 'three');

        $this->assertNull($list->one);
        $this->assertNull($list->four);

        $this->assertNotNull($list->two);
        $this->assertNotNull($list->three);
    }
}
 