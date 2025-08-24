<?php

/**
 * Debug Twig Configuration
 *
 * Tests the Twig configuration to see what paths it's using.
 *
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

echo "🔍 Testing Twig Configuration\n";
echo "=============================\n\n";

// Initialize application
$app = new \IslamWiki\Core\Application . '/..');
$container = $app->getContainer();

echo "✅ Application initialized\n";

// Test 1: Check ViewServiceProvider path calculation
echo "\n📊 Test 1: ViewServiceProvider Path Check\n";
echo "==========================================\n";

// Simulate the ViewServiceProvider path calculation
$basePath = dirname(__DIR__, 2); // This is what ViewServiceProvider uses
$templatePath = $basePath . '/resources/views';

echo "Base path (dirname(__DIR__, 2)): $basePath\n";
echo "Template path: $templatePath\n";
echo "Template path exists: " . (is_dir($templatePath) ? 'Yes' : 'No') . "\n";

// Test 2: Check actual template path
echo "\n📊 Test 2: Actual Template Path Check\n";
echo "=====================================\n";

$actualTemplatePath = __DIR__ . '/../resources/views/settings/index.twig';
echo "Actual template path: $actualTemplatePath\n";
echo "Template exists: " . (file_exists($actualTemplatePath) ? 'Yes' : 'No') . "\n";

// Test 3: Check Twig loader paths
echo "\n📊 Test 3: Twig Loader Path Check\n";
echo "==================================\n";

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
            echo "    Exists: " . (is_dir($path) ? 'Yes' : 'No') . "\n";
            echo "    Readable: " . (is_readable($path) ? 'Yes' : 'No') . "\n";

            if (is_dir($path)) {
                $files = scandir($path);
                $temp_a9d27d7d = implode(', ', array_filter($files, function ($f) {
                    return $f !== '.' && $f !== '..';
                }));
                echo "    Contents: " . $temp_a9d27d7d;
            }
        }
    }
} catch (\Exception $e) {
    echo "❌ Twig loader error: " . $e->getMessage() . "\n";
}

// Test 4: Test with correct path
echo "\n📊 Test 4: Test with Correct Path\n";
echo "==================================\n";

try {
    // Create a TwigRenderer with the correct path
    $correctTemplatePath = __DIR__ . '/../resources/views';
    echo "Correct template path: $correctTemplatePath\n";
        $temp_3e3437d7 = (is_dir($correctTemplatePath) ? 'Yes' : 'No') . "\n";
        echo "Correct template path exists: " . $temp_3e3437d7;

    $twigRenderer = new \IslamWiki\Core\View\TwigRenderer($correctTemplatePath, false, true);

    // Test rendering
    $result = $twigRenderer->render('settings/index', ['title' => 'Test']);

    echo "✅ Template rendered successfully with correct path\n";
    echo "Result length: " . strlen($result) . " characters\n";

    if (strpos($result, 'skin-card') !== false) {
        echo "✅ Response contains skin cards\n";
    } else {
        echo "❌ Response does not contain skin cards\n";
    }
} catch (\Exception $e) {
    echo "❌ Correct path test error: " . $e->getMessage() . "\n";
    echo "Error type: " . get_class($e) . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\n✅ Twig configuration test completed!\n";
