<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/10/14
 * Time: 8:52 PM
 */

namespace tests;


use ptejada\uFlex\Hash;

class HashTest extends \PHPUnit_Framework_TestCase {


    public function testGenerator()
    {
        $hash = new Hash();
        $partial = $hash->generate();

        $uid = 5;
        $code = $hash->generate($uid, $partial);

        $this->assertEquals(40, strlen($code));

        list($expectedUID, $expectedPartial) = $hash->examine($code);

        $this->assertEquals($expectedUID, $uid);
        $this->assertGreaterThanOrEqual(0, strpos($partial, $expectedPartial));
    }

    public function testEncoderDecoder()
    {
        $hash = new Hash();

        /*
         * Generate 50 random assertions to test ability
         * to encode an integer into a hash and extract it
         */
        for($i = 0; $i<50; $i++)
        {
            // Generate a random number
            $expectedNumber = rand(1,999999);
            $code = $hash->generate($expectedNumber);

            list($actualNumber) = $hash->examine($code);

            $this->assertEquals($expectedNumber, $actualNumber);
        }
    }
}
 