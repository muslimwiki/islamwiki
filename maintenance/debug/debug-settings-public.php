<?php

/**
 * Public Settings Debug Page
 *
 * A temporary public version of the settings page for testing skin display.
 *
 * @package IslamWiki\Debug
 * @version 0.0.28
 * @license AGPL-3.0-only
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

// Simulate the SettingsController logic without authentication
function discoverAvailableSkins(): array
{
    $skinsDir = __DIR__ . '/../skins';
    $availableSkins = [];

    if (!is_dir($skinsDir)) {
        return $availableSkins;
    }

    $skinDirs = glob($skinsDir . '/*', GLOB_ONLYDIR);

    foreach ($skinDirs as $skinDir) {
        $skinName = basename($skinDir);
        $skinConfigFile = $skinDir . '/skin.json';

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
                }
            } catch (\Exception $e) {
                // Log error but continue
            }
        }
    }

    return $availableSkins;
}

function simulateSkinManager(): array
{
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

    foreach ($availableSkins as $skinKey => $skinData) {
        $lowerSkinName = strtolower($skinData['name']);

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
        }
    }

    return $skinOptions;
}

// Generate the data
$availableSkins = discoverAvailableSkins();
$loadedSkins = simulateSkinManager();
$skinOptions = processSkinOptions($availableSkins, $loadedSkins);

$templateData = [
    'title' => 'Settings - IslamWiki (Debug)',
    'user' => null,
    'skinOptions' => $skinOptions,
    'activeSkin' => 'bismillah',
    'availableSkins' => $availableSkins,
    'userSettings' => []
];

// Load the template
$templatePath = __DIR__ . '/../resources/views/settings/index.twig';
if (!file_exists($templatePath)) {
    die("Template not found: $templatePath");
}

// Simple template rendering for debug
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($templateData['title']) ?></title>
    <link rel="stylesheet" href="/css/safa.css">
    <link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">
    <style>
        body { 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: linear-gradient(135deg, #4f46e5 0%, #4338ca 25%, #6366f1 50%, #8b5cf6 75%, #a855f7 100%);
            min-height: 100vh;
        }
        .debug-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
        }
        .debug-header h1 {
            color: #4f46e5;
            margin: 0;
        }
        .debug-header p {
            color: #6b7280;
            margin: 10px 0 0 0;
        }
    </style>
</head>
<body>
    <div class="debug-header">
        <h1>🔧 Settings Debug Page</h1>
        <p>This is a debug version of the settings page to test skin display functionality</p>
    </div>

    <div class="settings-container">
        <div class="container">
            <!-- Settings Header -->
            <div class="settings-header">
                <h1 class="settings-title">⚙️ Settings</h1>
                <p class="settings-subtitle">Manage your account and application preferences</p>
            </div>

            <!-- Settings Navigation -->
            <div class="settings-nav">
                <button class="nav-tab active" data-tab="appearance">
                    🎨 Appearance
                </button>
                <button class="nav-tab" data-tab="account">
                    👤 Account
                </button>
                <button class="nav-tab" data-tab="privacy">
                    🔒 Privacy
                </button>
                <button class="nav-tab" data-tab="notifications">
                    🔔 Notifications
                </button>
            </div>

            <!-- Settings Content -->
            <div class="settings-content">
                <!-- Appearance Tab -->
                <div class="settings-tab active" id="appearance">
                    <div class="settings-section">
                        <h2 class="section-title">🎨 Skin Selection</h2>
                        <p class="section-description">Choose the visual theme for your IslamWiki experience. New skins are automatically discovered and available for selection.</p>
                        
                        <div class="skin-grid">
                            <?php foreach ($templateData['skinOptions'] as $skinName => $skin) : ?>
                            <div class="skin-card <?= $skin['active'] ? 'active' : '' ?>" data-skin="<?= htmlspecialchars($skinName) ?>">
                                <div class="skin-info">
                                    <h3 class="skin-name"><?= htmlspecialchars($skin['name']) ?></h3>
                                    <p class="skin-description"><?= htmlspecialchars($skin['description']) ?></p>
                                    <div class="skin-meta">
                                        <span class="skin-version">v<?= htmlspecialchars($skin['version']) ?></span>
                                        <span class="skin-author">by <?= htmlspecialchars($skin['author']) ?></span>
                                    </div>
                                    
                                    <?php if (!empty($skin['features'])) : ?>
                                    <div class="skin-features">
                                        <span class="features-label">Features:</span>
                                        <?php foreach ($skin['features'] as $feature) : ?>
                                        <span class="feature-tag"><?= htmlspecialchars($feature) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="skin-actions">
                                        <?php if ($skin['active']) : ?>
                                            <button class="skin-select-btn" disabled>
                                                ✓ Active
                                            </button>
                                        <?php else : ?>
                                            <button class="skin-select-btn" data-skin="<?= htmlspecialchars($skinName) ?>">
                                                Select Skin
                                            </button>
                                        <?php endif; ?>
                                        
                                        <button class="skin-info-btn" data-skin="<?= htmlspecialchars($skinName) ?>">
                                            Info
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Simple tab navigation
        document.addEventListener('DOMContentLoaded', function() {
            const navTabs = document.querySelectorAll('.nav-tab');
            const settingsTabs = document.querySelectorAll('.settings-tab');
            
            navTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    const targetTab = this.dataset.tab;
                    
                    navTabs.forEach(t => t.classList.remove('active'));
                    settingsTabs.forEach(t => t.classList.remove('active'));
                    
                    this.classList.add('active');
                    document.getElementById(targetTab).classList.add('active');
                });
            });
        });
    </script>
</body>
</html> 