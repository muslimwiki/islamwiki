<?php

/**
 * Test page for Muslim skin
 *
 * This page demonstrates the Muslim skin working correctly.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Core/NizamApplication.php';
require_once __DIR__ . '/../src/Skins/SkinManager.php';

use IslamWiki\Core\NizamApplication;
use IslamWiki\Skins\SkinManager;

// Initialize application
$app = new NizamApplication(__DIR__ . '/..');

// Get skin manager and reload skins to ensure Muslim is loaded
$container = $app->getContainer();
$skinManager = $container->get('skin.manager');

// Force reload all skins to ensure Muslim is loaded
$skinManager->reloadAllSkins();

// Switch to Muslim skin for this test
$originalSkin = $skinManager->getActiveSkinName();
$skinManager->setActiveSkin('Muslim');

// Get the Muslim skin
$muslimSkin = $skinManager->getSkin('Muslim');

if (!$muslimSkin) {
    die("Error: Muslim skin not found!");
}

// Get skin assets
$cssContent = $muslimSkin->getCssContent();
$jsContent = $muslimSkin->getJsContent();
$layoutPath = $muslimSkin->getLayoutPath();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🕌 Muslim Skin Test - IslamWiki</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&family=Amiri:wght@400;700&display=swap" rel="stylesheet">
    
    <!-- Safa CSS Framework -->
    <link rel="stylesheet" href="/css/safa.css">
    
    <!-- Muslim Skin CSS -->
    <style>
        <?php echo $cssContent; ?>
    </style>
</head>
<body>
    <!-- Citizen-inspired Header Structure -->
    <header class="citizen-header">
        <!-- Top Bar -->
        <div class="citizen-header-top">
            <div class="citizen-header-container">
                <div class="citizen-header-left">
                    <a href="/" class="citizen-logo">
                        <span class="logo-icon">🕌</span>
                        <span class="logo-text">IslamWiki</span>
                    </a>
                </div>
                
                <div class="citizen-header-center">
                    <div class="citizen-search">
                        <form class="search-form" method="GET" action="/iqra-search">
                            <div class="search-input-wrapper">
                                <input type="text" name="q" class="search-input" 
                                       placeholder="Search Islamic knowledge..." 
                                       value="">
                                <button type="submit" class="search-button">
                                    <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                    </svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="citizen-header-right">
                    <nav class="citizen-nav">
                        <a href="/" class="nav-link active">Home</a>
                        <a href="/pages" class="nav-link">Browse</a>
                        <a href="/quran" class="nav-link">Quran</a>
                        <a href="/hadith" class="nav-link">Hadith</a>
                    </nav>
                    
                    <div class="citizen-user-menu">
                        <div class="auth-buttons">
                            <a href="/login" class="btn btn-outline">Sign In</a>
                            <a href="/register" class="btn btn-primary">Join</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Secondary Navigation -->
        <div class="citizen-header-secondary">
            <div class="citizen-header-container">
                <nav class="citizen-secondary-nav">
                    <a href="/islamic-sciences" class="nav-link">Islamic Sciences</a>
                    <a href="/prayer-times" class="nav-link">Prayer Times</a>
                    <a href="/calendar" class="nav-link">Islamic Calendar</a>
                    <a href="/community" class="nav-link">Community</a>
                    <a href="/about" class="nav-link">About</a>
                </nav>
            </div>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="citizen-main">
        <div class="citizen-container">
            <div class="card">
                <div class="card-header">
                    <h1 class="card-title">🕌 Muslim Skin Test Page</h1>
                </div>
                <div class="card-body">
                    <p>This page demonstrates the <strong>Muslim skin</strong> working correctly.</p>
                    
                    <h2>✅ Skin Information</h2>
                    <ul>
                        <li><strong>Name:</strong> <?php echo htmlspecialchars($muslimSkin->getName()); ?></li>
                        <li><strong>Version:</strong> <?php echo htmlspecialchars($muslimSkin->getVersion()); ?></li>
                        <li><strong>Author:</strong> <?php echo htmlspecialchars($muslimSkin->getAuthor()); ?></li>
                        <li><strong>Description:</strong> <?php echo htmlspecialchars($muslimSkin->getDescription()); ?></li>
                    </ul>
                    
                    <h2>🎨 Design Features</h2>
                    <p>The Muslim skin is inspired by the Citizen MediaWiki skin and includes:</p>
                    <ul>
                        <li>Citizen-inspired header structure with top bar and secondary navigation</li>
                        <li>Responsive design that works on all devices</li>
                        <li>Islamic color scheme with blue and gold accents</li>
                        <li>Modern typography with Roboto and Amiri fonts</li>
                        <li>Smooth animations and transitions</li>
                        <li>Accessibility features for better usability</li>
                        <li>Dark theme support</li>
                    </ul>
                    
                    <h2>🔧 Technical Details</h2>
                    <ul>
                        <li><strong>CSS Size:</strong> <?php echo number_format(strlen($cssContent)); ?> bytes</li>
                        <li><strong>JS Size:</strong> <?php echo number_format(strlen($jsContent)); ?> bytes</li>
                        <li><strong>Layout Template:</strong> <?php echo htmlspecialchars($layoutPath); ?></li>
                        <li><strong>Active Skin:</strong> <?php echo htmlspecialchars($skinManager->getActiveSkinName()); ?></li>
                    </ul>
                    
                    <div class="alert alert-success">
                        <strong>Success!</strong> The Muslim skin is working correctly.
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>Info:</strong> This skin follows the Citizen MediaWiki design patterns while maintaining Islamic aesthetics.
                    </div>
                </div>
            </div>
            
            <!-- Test different components -->
            <div class="card mt-4">
                <div class="card-header">
                    <h2 class="card-title">🧪 Component Tests</h2>
                </div>
                <div class="card-body">
                    <h3>Buttons</h3>
                    <p>
                        <button class="btn btn-primary">Primary Button</button>
                        <button class="btn btn-outline">Outline Button</button>
                        <button class="btn btn-secondary">Secondary Button</button>
                    </p>
                    
                    <h3>Alerts</h3>
                    <div class="alert alert-success">Success alert</div>
                    <div class="alert alert-error">Error alert</div>
                    <div class="alert alert-warning">Warning alert</div>
                    <div class="alert alert-info">Info alert</div>
                    
                    <h3>Navigation Links</h3>
                    <nav class="citizen-nav">
                        <a href="#" class="nav-link active">Active Link</a>
                        <a href="#" class="nav-link">Normal Link</a>
                        <a href="#" class="nav-link">Another Link</a>
                    </nav>
                </div>
            </div>
        </div>
    </main>

    <!-- Citizen-inspired Footer -->
    <footer class="citizen-footer">
        <div class="citizen-header-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>IslamWiki</h3>
                    <p>Your comprehensive source for Islamic knowledge and community.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="/pages">Browse Pages</a></li>
                        <li><a href="/quran">Quran</a></li>
                        <li><a href="/hadith">Hadith</a></li>
                        <li><a href="/community">Community</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Resources</h4>
                    <ul>
                        <li><a href="/prayer-times">Prayer Times</a></li>
                        <li><a href="/calendar">Islamic Calendar</a></li>
                        <li><a href="/islamic-sciences">Islamic Sciences</a></li>
                        <li><a href="/about">About</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <ul>
                        <li><a href="/help">Help Center</a></li>
                        <li><a href="/contact">Contact Us</a></li>
                        <li><a href="/privacy">Privacy Policy</a></li>
                        <li><a href="/terms">Terms of Service</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 IslamWiki. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <!-- Muslim Skin JavaScript -->
    <script>
        <?php echo $jsContent; ?>
    </script>
</body>
</html>

<?php
// Switch back to original skin
$skinManager->setActiveSkin($originalSkin);
?> 