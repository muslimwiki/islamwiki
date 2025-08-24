<?php

/**
 * Debug Template Name Resolution
 *
 * Tests the template name resolution to see if the issue is with the template name.
 *
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Template Name Resolution\n";
echo "==================================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application . '/..');
$container = $app->getContainer();

echo "✅ Application initialized\n";

// Test 1: Check template file directly
echo "\n📊 Test 1: Direct Template File Check\n";
echo "=====================================\n";

$templatePath = __DIR__ . '/../resources/views';
$templateFile = $templatePath . '/settings/index.twig';

echo "Template path: $templatePath\n";
echo "Template file: $templateFile\n";
echo "Template file exists: " . (file_exists($templateFile) ? 'Yes' : 'No') . "\n";

if (file_exists($templateFile)) {
    echo "Template file size: " . filesize($templateFile) . " bytes\n";
        $temp_f0c47aa6 = (is_readable($templateFile) ? 'Yes' : 'No') . "\n";
        echo "Template file readable: " . $temp_f0c47aa6;
}

// Test 2: Test different template name formats
echo "\n📊 Test 2: Template Name Formats\n";
echo "================================\n";

try {
    $view = $container->get('view');

    $testNames = [
        'settings/index',
        'settings/index.twig',
        'settings/index.html.twig',
        'settings/index.html',
        'settings/index.php'
    ];

    foreach ($testNames as $name) {
        echo "Testing template name: '$name'\n";
        try {
            $result = $view->render($name, ['title' => 'Test']);
            $temp_cc44dba8 = strlen($result) . " characters\n";
            echo "  ✅ SUCCESS - Result length: " . $temp_cc44dba8;
            if (strlen($result) > 0) {
                echo "  First 100 chars: " . substr($result, 0, 100) . "...\n";
            }
            break; // Found working template name
        } catch (\Exception $e) {
            echo "  ❌ FAILED - " . $e->getMessage() . "\n";
        }
    }
} catch (\Exception $e) {
    echo "❌ View system error: " . $e->getMessage() . "\n";
}

// Test 3: Check Twig loader configuration
echo "\n📊 Test 3: Twig Loader Configuration\n";
echo "=====================================\n";

try {
    $view = $container->get('view');
    $twig = $view->getTwig();
    $loader = $twig->getLoader();

    echo "Twig loader class: " . get_class($loader) . "\n";

    if ($loader instanceof \Twig\Loader\FilesystemLoader) {
        $paths = $loader->getPaths();
        echo "Twig loader paths:\n";
        foreach ($paths as $path) {
            echo "  - $path\n";

            // Check if settings directory exists
            $settingsDir = $path . '/settings';
            echo "    Settings dir: $settingsDir\n";
            $temp_4d7f714e = (is_dir($settingsDir) ? 'Yes' : 'No') . "\n";
            echo "    Settings dir exists: " . $temp_4d7f714e;

            if (is_dir($settingsDir)) {
                $files = scandir($settingsDir);
                $temp_a9d27d7d = implode(', ', array_filter($files, function ($f) {
                    return $f !== '.' && $f !== '..';
                }));
                echo "    Settings files: " . $temp_a9d27d7d;
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Twig loader error: " . $e->getMessage() . "\n";
}

// Test 4: Test with raw Twig
echo "\n📊 Test 4: Raw Twig Test\n";
echo "========================\n";

try {
    $templatePath = __DIR__ . '/../resources/views';
    $loader = new \Twig\Loader\FilesystemLoader($templatePath);
    $twig = new \Twig\Environment($loader, ['debug' => true]);

    echo "Created Twig environment with path: $templatePath\n";

    // Test template loading
    $template = $twig->load('settings/index.twig');
    echo "✅ Template loaded successfully\n";

    // Test rendering
    $result = $template->render(['title' => 'Test']);
    echo "✅ Template rendered successfully\n";
    echo "Result length: " . strlen($result) . " characters\n";

    if (strpos($result, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
} catch (\Exception $e) {
    echo "❌ Raw Twig test error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n✅ Template name resolution test completed!\n";
