<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 6/1/14
 * Time: 3:13 PM
 */

namespace tests;


use ptejada\uFlex\LinkedCollection;

class LinkedCollectionTest extends \PHPUnit_Framework_TestCase {

    public function testFilter()
    {
        $arrayList = array(
            'one' => 1,
            'two' => 2,
            'three' => 3,
            'four' => 4,
        );

        $list = new LinkedCollection($arrayList);

        $this->assertNotNull($list->one);
        $this->assertNotNull($list->four);

        $list->filter('two', 'three');

        $this->assertNull($list->one);
        $this->assertNull($list->four);

        $this->assertNotNull($list->two);
        $this->assertNotNull($list->three);

        $this->assertEquals($arrayList, $list->toArray());
    }
}
 