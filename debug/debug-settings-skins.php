<?php

/**
 * Debug Settings Skins Display
 *
 * Tests the skin discovery and display functionality without authentication.
 *
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Simulate the SettingsController's skin discovery and processing
function testSkinDiscovery(): array
{
    $skinsDir = __DIR__ . '/../skins';
    $availableSkins = [];

    if (!is_dir($skinsDir)) {
        echo "❌ Skins directory not found: $skinsDir\n";
        return $availableSkins;
    }

    echo "✅ Skins directory found: $skinsDir\n";

    $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);
    echo "📁 Found " . count($skinDirs) . " skin directories:\n";

    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $skinConfigFile = $skinDir . '/skin.json';

        echo "  - $skinName: ";

        if (file_exists($skinConfigFile)) {
            try {
                $config = json_decode(file_get_contents($skinConfigFile), true);

                if ($config && isset($config['name'])) {
                    $availableSkins[strtolower($skinName)] = [
                        'name' => $config['name'],
                        'version' => $config['version'] ?? '0.0.1',
                        'author' => $config['author'] ?? 'Unknown',
                        'description' => $config['description'] ?? '',
                        'directory' => $skinName,
                        'features' => $config['features'] ?? [],
                        'config' => $config['config'] ?? [],
                        'dependencies' => $config['dependencies'] ?? []
                    ];
                    echo "✅ Loaded successfully\n";
                } else {
                    echo "❌ Invalid config (missing name)\n";
                }
            } catch (\Exception $e) {
                echo "❌ Error loading config: " . $e->getMessage() . "\n";
            }
        } else {
            echo "❌ Config file not found\n";
        }
    }

    return $availableSkins;
}

function simulateSkinManager(): array
{
    // Simulate the skin manager's loaded skins with proper objects
    return [
        'bismillah' => new class {
            public function getName(): string
            {
                return 'Bismillah';
            }
            public function getVersion(): string
            {
                return '0.0.28';
            }
            public function getAuthor(): string
            {
                return 'IslamWiki Team';
            }
            public function getDescription(): string
            {
                return 'The default skin for IslamWiki with modern Islamic design and beautiful gradients.';
            }
        },
        'muslim' => new class {
            public function getName(): string
            {
                return 'Muslim';
            }
            public function getVersion(): string
            {
                return '0.0.1';
            }
            public function getAuthor(): string
            {
                return 'IslamWiki Team';
            }
            public function getDescription(): string
            {
                return 'A beautiful, usable, responsive skin inspired by Citizen MediaWiki skin with Islamic design elements.';
            }
        }
    ];
}

function processSkinOptions(array $availableSkins, array $loadedSkins, string $userActiveSkin = 'bismillah'): array
{
    $skinOptions = [];

    echo "\n🔄 Processing skin options:\n";

    foreach ($availableSkins as $skinKey => $skinData) {
        $lowerSkinName = strtolower($skinData['name']);
        echo "  - Processing {$skinData['name']} (lowercase: $lowerSkinName): ";

        if (isset($loadedSkins[$lowerSkinName])) {
            $skin = $loadedSkins[$lowerSkinName];

            $isActive = $lowerSkinName === strtolower($userActiveSkin);

            $skinOptions[$skinData['name']] = [
                'name' => $skin->getName(),
                'version' => $skin->getVersion(),
                'author' => $skin->getAuthor(),
                'description' => $skin->getDescription(),
                'active' => $isActive,
                'css_key' => $lowerSkinName,
                'directory' => $skinData['directory'],
                'features' => $skinData['features'] ?? [],
                'config' => $skinData['config'] ?? []
            ];

            $temp_3304dfc7 = ($isActive ? 'yes' : 'no') . ")\n";
            echo "✅ Added to options (active: " . $temp_3304dfc7;
        } else {
            echo "❌ Not found in loaded skins\n";
        }
    }

    return $skinOptions;
}

echo "🔍 Testing Settings Skin Display Logic\n";
echo "=====================================\n\n";

// Step 1: Discover available skins
$availableSkins = testSkinDiscovery();

// Step 2: Simulate skin manager
$loadedSkins = simulateSkinManager();

// Step 3: Process skin options
$skinOptions = processSkinOptions($availableSkins, $loadedSkins);

echo "\n📊 Final Results:\n";
echo "================\n";

if (empty($skinOptions)) {
    echo "❌ No skin options generated\n";
} else {
    echo "✅ Generated " . count($skinOptions) . " skin options:\n\n";

    foreach ($skinOptions as $skinName => $skinData) {
        echo "🎨 {$skinData['name']} (v{$skinData['version']})\n";
        echo "   Author: {$skinData['author']}\n";
        echo "   Description: {$skinData['description']}\n";
        echo "   Directory: {$skinData['directory']}\n";
        echo "   Active: " . ($skinData['active'] ? 'Yes' : 'No') . "\n";
        echo "   CSS Key: {$skinData['css_key']}\n";

        if (!empty($skinData['features'])) {
            echo "   Features: " . implode(', ', $skinData['features']) . "\n";
        }

        echo "\n";
    }
}

echo "✅ Settings skin display test completed!\n";

// Test the template rendering logic
echo "\n🧪 Testing Template Variables:\n";
echo "==============================\n";

$templateData = [
    'title' => 'Settings - IslamWiki',
    'user' => null,
    'skinOptions' => $skinOptions,
    'activeSkin' => 'bismillah',
    'availableSkins' => $availableSkins,
    'userSettings' => []
];

echo "Template data structure:\n";
foreach ($templateData as $key => $value) {
    if (is_array($value)) {
        echo "  - $key: " . count($value) . " items\n";
    } else {
        echo "  - $key: " . (is_string($value) ? $value : gettype($value)) . "\n";
    }
}

echo "\n✅ Template data test completed!\n";
