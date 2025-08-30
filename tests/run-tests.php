<?php

/**
 * Test Runner for IslamWiki
 * 
 * This script provides a custom test runner that doesn't rely on phpunit.xml
 */

// Load the Composer autoloader
require_once __DIR__ . '/../vendor/autoload.php';

// Load the test helper
require_once __DIR__ . '/TestHelper.php';

// Use the PHPUnit TextUI TestRunner
use PHPUnit\TextUI\TestRunner;

// Define test suites
$testSuites = [
    'unit' => [
        'directory' => __DIR__ . '/Unit',
        'suffix' => 'Test.php',
    ],
    'feature' => [
        'directory' => __DIR__ . '/Feature',
        'suffix' => 'Test.php',
    ],
];

// Get command line arguments
$options = getopt('', ['filter:', 'testsuite::', 'coverage', 'coverage-html::']);

// Configure PHPUnit
$arguments = [
    'verbose' => true,
    'colors' => 'always',
    'stopOnError' => false,
    'stopOnFailure' => false,
    'stopOnIncomplete' => false,
    'stopOnSkipped' => false,
    'stopOnRisky' => false,
    'stopOnWarning' => false,
];

// Set up code coverage if requested
if (isset($options['coverage']) || isset($options['coverage-html'])) {
    if (!extension_loaded('xdebug') && !extension_loaded('pcov')) {
        echo "Warning: Xdebug or PCOV extension is required for code coverage.\n";
    } else {
        $filter = new \SebastianBergmann\CodeCoverage\Filter();
        $filter->includeDirectory(__DIR__ . '/../app');
        
        $driver = null;
        if (extension_loaded('pcov')) {
            $driver = new \SebastianBergmann\CodeCoverage\Driver\PcovDriver();
        } elseif (extension_loaded('xdebug') && version_compare(phpversion('xdebug'), '3.0.0', '>=')) {
            $driver = new \SebastianBergmann\CodeCoverage\Driver\Xdebug3Driver();
        } elseif (extension_loaded('xdebug')) {
            $driver = new \SebastianBergmann\CodeCoverage\Driver\Xdebug2Driver();
        }
        
        if ($driver) {
            $coverage = new \SebastianBergmann\CodeCoverage\CodeCoverage($driver, $filter);
            $coverage->start('TestRun');
            
            // Register shutdown function to save coverage data
            register_shutdown_function(function () use ($coverage) {
                $coverage->stop();
                
                $writer = new \SebastianBergmann\CodeCoverage\Report\Clover();
                $writer->process($coverage, __DIR__ . '/../coverage.xml');
                
                if (in_array('--coverage-html', $GLOBALS['argv'])) {
                    $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade();
                    $writer->process($coverage, __DIR__ . '/../coverage-report');
                }
            });
        }
    }
}

// Add test files
$testSuite = new PHPUnit\Framework\TestSuite('IslamWiki Test Suite');

// Determine which test suites to run
$suitesToRun = isset($options['testsuite']) ? explode(',', $options['testsuite']) : ['unit', 'feature'];

foreach ($suitesToRun as $suiteName) {
    if (!isset($testSuites[$suiteName])) {
        echo "Unknown test suite: $suiteName\n";
        continue;
    }
    
    $suite = $testSuites[$suiteName];
    $directory = new RecursiveDirectoryIterator($suite['directory']);
    $iterator = new RecursiveIteratorIterator($directory);
    $testFiles = new RegexIterator($iterator, '/^.+'.$suite['suffix'].'$/i', RecursiveRegexIterator::GET_MATCH);
    
    foreach ($testFiles as $file) {
        $testSuite->addTestFile($file[0]);
    }
}

// Apply test filter if specified
if (isset($options['filter'])) {
    $arguments['filter'] = $options['filter'];
}

// Run the tests
$runner = new TestRunner();

// Create a test suite with all test files
$testSuite = new PHPUnit\Framework\TestSuite('IslamWiki Tests');

// Add test files to the suite
$testFiles = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator(__DIR__ . '/Unit'),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($testFiles as $file) {
    if (preg_match('/Test\.php$/', $file->getFilename())) {
        $testSuite->addTestFile($file->getPathname());
    }
}

// Run the tests
$result = $runner->run($testSuite, $arguments, false);

exit($result->wasSuccessful() ? 0 : 1);
