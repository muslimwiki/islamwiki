<?php

declare(strict_types=1);

/**
 * Simple Template Test Script
 * 
 * This script tests the basic template functionality without the TemplateEngine.
 * 
 * Usage: php scripts/templates/simple_template_test.php
 */

require_once __DIR__ . '/../../vendor/autoload.php';

use IslamWiki\Core\Database\Connection;
use IslamWiki\Models\Template;

echo "🧪 Simple Template System Test\n";
echo "==============================\n\n";

// Initialize database connection
$config = [
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'islamwiki',
    'username' => 'root',
    'password' => '',
    'charset' => 'utf8mb4',
    'collation' => 'utf8mb4_unicode_ci',
];

try {
    $connection = new Connection($config);
    echo "✅ Database connection established\n";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    exit(1);
}

// Test 1: List all templates
echo "\n📝 Test 1: List All Templates\n";
echo "-------------------------------\n";

try {
    $templates = Template::getAllActive($connection);
    echo "✅ Found " . count($templates) . " active templates:\n";
    
    foreach ($templates as $template) {
        echo "   • " . $template->getName() . " (" . $template->getCategory() . ")\n";
        echo "     Description: " . $template->getDescription() . "\n";
        echo "     Parameters: " . json_encode($template->getParameters()) . "\n";
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Template listing failed: " . $e->getMessage() . "\n";
}

// Test 2: Test specific template
echo "\n📝 Test 2: Test QuranVerse Template\n";
echo "------------------------------------\n";

try {
    $template = Template::findByName($connection, 'QuranVerse');
    if ($template) {
        echo "✅ Found QuranVerse template\n";
        
        // Test rendering with parameters
        $parameters = [
            'surah' => 1,
            'ayah' => 1,
            'surah_name' => 'Al-Fatiha',
            'translation' => 'In the name of Allah, the Entirely Merciful, the Especially Merciful'
        ];
        
        $rendered = $template->render($parameters);
        echo "✅ Template rendered successfully\n";
        echo "   Rendered HTML length: " . strlen($rendered) . " characters\n";
        echo "   Preview: " . substr($rendered, 0, 100) . "...\n";
        
    } else {
        echo "❌ QuranVerse template not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Template test failed: " . $e->getMessage() . "\n";
}

// Test 3: Test template categories
echo "\n📝 Test 3: Template Categories\n";
echo "--------------------------------\n";

try {
    $categories = ['Islamic', 'General', 'Navigation'];
    
    foreach ($categories as $category) {
        $templates = Template::getByCategory($connection, $category);
        echo "✅ " . $category . " category: " . count($templates) . " templates\n";
        
        foreach ($templates as $template) {
            echo "   • " . $template->getName() . "\n";
        }
        echo "\n";
    }
    
} catch (Exception $e) {
    echo "❌ Category test failed: " . $e->getMessage() . "\n";
}

// Test 4: Test template parameters
echo "\n📝 Test 4: Template Parameters\n";
echo "--------------------------------\n";

try {
    $template = Template::findByName($connection, 'HadithCitation');
    if ($template) {
        echo "✅ Found HadithCitation template\n";
        $parameters = $template->getParameters();
        echo "   Parameter count: " . count($parameters) . "\n";
        
        foreach ($parameters as $name => $config) {
            echo "   • " . $name . " (" . $config['type'] . ")";
            if (isset($config['required']) && $config['required']) {
                echo " - REQUIRED";
            }
            echo "\n";
            echo "     Description: " . $config['description'] . "\n";
        }
        
    } else {
        echo "❌ HadithCitation template not found\n";
    }
    
} catch (Exception $e) {
    echo "❌ Parameter test failed: " . $e->getMessage() . "\n";
}

echo "\n🎉 Simple template system testing completed!\n";
echo "\n🔧 Next steps:\n";
echo "  1. Visit: http://localhost/test_templates.php\n";
echo "  2. Test templates in wiki pages\n";
echo "  3. Create custom templates as needed\n"; 