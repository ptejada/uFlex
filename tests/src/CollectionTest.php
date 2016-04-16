<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 8/13/14
 * Time: 11:40 AM
 */

namespace tests;


use ptejada\uFlex\Classes\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testGetters()
    {
        $info = array(
            'string'   => 'Hello World',
            'zero'     => 0,
            'empty'    => '',
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

        $list->locations      = array();
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
                                   'one'   => 1,
                                   'two'   => 2,
                                   'three' => 3,
                                   'four'  => 4,
                               ));

        $this->assertTrue(isset($list->one));
        $this->assertFalse(isset($list->five));

        $this->assertNull($list->five);
        $this->assertNull($list->get('two.one'));
    }

    public function testUnsetter()
    {
        $list = new Collection(array(
                                   'one'   => array(
                                       'two' => 2
                                   ),
                                   'two'   => 2,
                                   'three' => 3,
                                   'four'  => 4,
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
                                   'one'   => array(
                                       'two' => 2
                                   ),
                                   'two'   => 2,
                                   'three' => 3,
                                   'four'  => 4,
                               ));

        error_reporting(E_ALL);

        $this->assertEquals(array('two' => 2), $list->get('one')->toArray());
        $this->assertEquals(2, $list->get('one.two'));
        $this->assertEquals(null, $list->get('one.two.three'));

    }

    public function testStringSetter()
    {
        $list = new Collection(array(
                                   'one'   => array(
                                       'two' => 2
                                   ),
                                   'two'   => 2,
                                   'three' => 3,
                                   'four'  => 4,
                               ));

        error_reporting(E_ALL);

        $list->set('five', 5);
        $this->assertEquals(5, $list->five);

        $list->set('one.two.three', 3);
        $this->assertEquals(3, $list->one->two->three);

        $list->set('two.three.four.five', array(1, 1, 1, 1, 1));
        $this->assertEquals(array(1, 1, 1, 1, 1), $list->two->three->four->five->toArray());
    }

    public function testFilter()
    {
        $list = new Collection(array(
                                   'one'   => 1,
                                   'two'   => 2,
                                   'three' => 3,
                                   'four'  => 4,
                               ));

        $this->assertNotNull($list->one);
        $this->assertNotNull($list->four);

        $list->filter('two', 'three');

        $this->assertNull($list->one);
        $this->assertNull($list->four);

        $this->assertNotNull($list->two);
        $this->assertNotNull($list->three);
    }

    public function testUpdate()
    {
        $list = new Collection(array('one' => 123));

        $this->assertEquals(123, $list->one);

        $updates1 = array(1 => 'one', 2 => 'two', 3 => 'three');
        $list->update('one.two.three', $updates1);

        $this->assertEquals($updates1, $list->one->two->three->toArray());

        $updates2 = array(1 => 'uno', 4 => 'four');
        $list->update('one.two.three', $updates2);

        $this->assertEquals($updates2 + $updates1, $list->one->two->three->toArray());
    }

    public function testIterator()
    {
        $list = new Collection(array(0, 1, 2, 3, 4, 5, 6));

        foreach ($list as $key => $value) {
            $this->assertEquals($key, $value);
        }

        $list = new Collection(array(
                                   'one'   => 'one',
                                   'two'   => 'two',
                                   'three' => 'three',
                               )
        );

        foreach ($list as $key => $value) {
            $this->assertEquals($key, $value);
        }

        $list = new Collection(array(
                                   'one'   => array(1, 2, 3),
                                   'two'   => array(1, 2, 3),
                                   'three' => array(1, 2, 3),
                               )
        );

        foreach ($list as $key => $value) {
            $this->assertInstanceOf('ptejada\uFlex\Classes\LinkedCollection', $value);
        }
    }

    public function testAutoEscaping()
    {
        $raw = array(
            '><',
            '<div>',
            '<script>',
            '\>'
        );

        $list = new Collection($raw, true);

        foreach ($list as $key => $value) {
            $this->assertNotEquals($raw[$key], $value);
        }

        /*
         * Test deep auto escaping
         */
        $list = new Collection(
            array(
                array(
                    array($raw)
                )
            )
        );

        $list->setAutoEscape(true);

        foreach ($list as $value3) {
            foreach ($value3 as $value2) {
                foreach ($value2 as $value1) {
                    foreach ($value1 as $key => $value) {
                        $this->assertNotEquals($raw[$key], $value);
                    }
                }
            }
        }
    }

    public function testDelete()
    {
        $raw = array(
            'one' => array(
                'two' => array(
                    'three' => array()
                )
            )
        );

        $path  = 'one.two.three';
        $path2 = 'one.two';
        $path3 = 'one';

        $data = new Collection($raw);
        $this->assertInstanceOf('ptejada\uFlex\Classes\Collection', $data->get($path));

        $data->delete($path);
        $this->assertNull($data->get($path));

        $data->delete($path2);
        $data->delete($path);

        $this->assertNull($data->get($path));
        $this->assertNull($data->get($path2));

        $data->delete($path3);
        $this->assertNull($data->get($path3));
    }

    public function testFlatten()
    {
        $raw = array(
            'one'  => array(
                'two' => array(
                    'three' => array()
                ),
                'six' => array(
                    'seven' => array()
                )
            ),
            'four' => true
        );

        $data = new Collection($raw);

        $arr = $data->flatten();

        $this->assertArrayHasKey('four', $arr);
        $this->assertArrayHasKey('one.two.three', $arr);
        $this->assertArrayHasKey('one.six.seven', $arr);

    }

    public function testExpand()
    {
        $raw = array(
            'one|two|three' => array(),
            'one|six|seven' => array(),
            'four'          => true, // Note: this is excluded from the expected array
        );

        $expected = array(
            'one' => array(
                'two' => array(
                    'three' => array()
                ),
                'six' => array(
                    'seven' => array()
                )
            )
        );

        $data = new Collection();
        $data->setSeparator('|');
        $data->expand($raw, '|');

        $this->assertEquals($expected, $data->toArray());
        $this->assertInstanceOf('ptejada\uFlex\Classes\Collection', $data->get('one|two|three'));
    }
}
