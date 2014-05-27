<?php
/**
 * Created by PhpStorm.
 * User: Pablo
 * Date: 3/1/14
 * Time: 12:20 PM
 */

namespace tests;


use ptejada\uFlex\Log;

class LogTest extends \PHPUnit_Framework_TestCase {
    /** @var  Log */
    protected $log;

    public function setUp()
    {
        $this->log = new Log('Test');
        $this->log->channel('chan'.rand());
    }

    public function testDefaultNamespace()
    {
        $this->assertEquals('Test', $this->log->getNamespace(), 'Default default namespace');
    }

    public function testChangingChannel()
    {
        $this->log->error('Hello World');
        $this->assertTrue($this->log->hasError(), 'The test default channel has error');

        // Change channel
        $this->log->channel('chan'.rand());
        $this->assertFalse($this->log->hasError(), 'The new channel should have no errors');
    }



    public function testPredefinedErrors()
    {
        // The current channel error stack
        $errors = &$this->log->getErrors();

        // Test pristine state
        $this->assertInternalType('array', $errors, 'Initially the error stack should be an array');
        $this->assertEmpty($errors, 'Initially the error stack should also be an empty array');

        $errorList = array(
            404 => 'Not Found',
            403 => 'Forbidden',
            201 => 'Success',
        );

        // Update the list for the first time
        $this->log->updateErrorList($errorList);

        $this->log->error(404);
        $this->assertEquals('Not Found', $errors[0], 'Error list first update');

        // Update the list a second time
        $this->log->updateErrorList(array(101=>'Help'));

        $this->log->error(101);
        $this->assertEquals('Help', $errors[1], 'Error list second update');

        // Test updating existing predefined errors
        $this->log->updateErrorList(array(404=>'Changed'));

        $this->log->error(404);
        $this->assertEquals('Changed', $errors[2], 'Error list third update replaces existing entry');

        // Test original errors from the first update are still available
        $this->log->error(403);
        $this->assertEquals('Forbidden', $errors[3], 'Error list testing error from first update last');
    }

    public function testFormErrors()
    {
        $this->assertFalse($this->log->hasError(), 'No initial errors');

        $this->log->formError('name', 'The name is not valid');
        $this->assertTrue($this->log->hasError(), 'Error should be present');

        // The error for field 'name' should be present
        $this->assertArrayHasKey('name',$this->log->getFormErrors(), 'The field \'name\' should have an error');

        $this->assertEquals(2, count($this->log->getReports()), 'There should only be two report entry');
        $this->assertEquals(1, count($this->log->getErrors()), 'There should only be one error entry');
    }

    public function testErrors()
    {
        $errors = &$this->log->getErrors();
        $reports = &$this->log->getReports();

        $this->assertEquals(0, count($errors), 'No initial errors');
        $this->assertEquals(1, count($reports), 'Only The initial channel report');

        for ($i=0; $i<10; $i++)
        {
            $this->log->error('Hello world ' . $i);
        }

        $this->assertEquals(10, count($errors), 'There should be errors');
        $this->assertEquals(11, count($reports), 'There should be reports');

        $this->assertEquals(count($reports), count($reports), 'There should be the same amount of errors and reports');

        foreach (array_slice($reports,1) as $report) {
            $this->assertRegExp('/Error:/', $report, 'All reports should be errors');
        }

    }

    public function testLinking()
    {
        $log1 = new Log('1');
        $console = &$log1->getFullConsole();
        $log1->error('Hello World');
        $this->assertNotEmpty($console['errors']);
        $this->assertEquals(1, count($console['errors']));

        $log2 = $log1->newChildLog('2');
        $log2->error('Hello World 2');
        $this->assertEquals(2, count($console['errors']));

    }

}
 