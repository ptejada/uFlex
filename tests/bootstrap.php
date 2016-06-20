<?php
include_once 'Tests_Database_TestCase.php';
include_once dirname(__DIR__) . '/vendor/autoload.php';

// Initialize the registry
\ptejada\uFlex\Classes\Registry::getInstance();
\ptejada\uFlex\Config::registerSession('\tests\Mocks\SessionMock');

