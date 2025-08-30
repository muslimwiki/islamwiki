<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Set up error reporting
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Simple test to check if PHPUnit is working
class SimpleTest extends \PHPUnit\Framework\TestCase
{
    public function testBasicTest(): void
    {
        $this->assertTrue(true, 'Basic test passed');
    }
}

// Run the test
$test = new SimpleTest('testBasicTest');
$result = new \PHPUnit\Framework\TestResult();
$test->run($result);

// Output the result
echo "Test completed with " . ($result->wasSuccessful() ? 'SUCCESS' : 'FAILURE') . "\n";
foreach ($result->errors() as $error) {
    echo "Error: " . $error->getMessage() . "\n";
}
foreach ($result->failures() as $failure) {
    echo "Failure: " . $failure->getMessage() . "\n";
}
