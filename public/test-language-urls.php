<?php
/**
 * Test: Language-Aware URL System
 * 
 * This script tests the new language-aware URL system to ensure
 * that language context is maintained across page navigation.
 */

// Start session to test language persistence
session_start();

// Get current language from session or URI
$currentLanguage = 'en';
if (isset($_SESSION['language'])) {
    $currentLanguage = $_SESSION['language'];
} else {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uri = ltrim($uri, '/');
    $segments = explode('/', $uri);
    $supportedLanguages = ['en', 'ar', 'ur', 'tr', 'id', 'ms', 'fa', 'he'];
    
    if (!empty($segments[0]) && in_array($segments[0], $supportedLanguages, true)) {
        $currentLanguage = $segments[0];
        $_SESSION['language'] = $currentLanguage;
    }
}

// Language data
$languages = [
    'en' => ['name' => 'English', 'flag' => '🇺🇸', 'direction' => 'ltr'],
    'ar' => ['name' => 'العربية', 'flag' => '🇸🇦', 'direction' => 'rtl'],
    'ur' => ['name' => 'اردو', 'flag' => '🇵🇰', 'direction' => 'rtl'],
    'tr' => ['name' => 'Türkçe', 'flag' => '🇹🇷', 'direction' => 'ltr'],
    'id' => ['name' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'direction' => 'ltr'],
    'ms' => ['name' => 'Bahasa Melayu', 'flag' => '🇲🇾', 'direction' => 'ltr'],
    'fa' => ['name' => 'فارسی', 'flag' => '🇮🇷', 'direction' => 'rtl'],
    'he' => ['name' => 'עברית', 'flag' => '🇮🇱', 'direction' => 'rtl']
];

$currentLangData = $languages[$currentLanguage];

// Helper function to generate language-aware URLs
function lang_url($path = '', $language = 'en') {
    if ($language === 'en') {
        return '/' . ltrim($path, '/');
    }
    return '/' . $language . '/' . ltrim($path, '/');
}

?>
<!DOCTYPE html>
<html lang="<?= $currentLanguage ?>" dir="<?= $currentLangData['direction'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🌍 Language-Aware URL System Test - <?= $currentLangData['name'] ?></title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
            direction: <?= $currentLangData['direction'] ?>;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 30px;
        }
        .language-info {
            background: #e8f4fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            text-align: center;
        }
        .language-flag {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .language-name {
            font-size: 24px;
            font-weight: bold;
            color: #2980b9;
            margin-bottom: 10px;
        }
        .language-direction {
            font-size: 16px;
            color: #7f8c8d;
        }
        .test-section {
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
        }
        .test-section h3 {
            color: #34495e;
            margin-top: 0;
        }
        .url-examples {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .url-example {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #007bff;
        }
        .url-example .label {
            font-weight: bold;
            color: #495057;
            margin-bottom: 5px;
        }
        .url-example .url {
            font-family: monospace;
            background: #e9ecef;
            padding: 8px;
            border-radius: 4px;
            color: #495057;
            word-break: break-all;
        }
        .navigation-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
        }
        .nav-link {
            display: block;
            padding: 15px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            text-align: center;
            transition: background-color 0.2s;
        }
        .nav-link:hover {
            background: #0056b3;
        }
        .language-switcher {
            text-align: center;
            margin-bottom: 30px;
        }
        .language-btn {
            display: inline-block;
            margin: 5px;
            padding: 10px 15px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background-color 0.2s;
        }
        .language-btn:hover {
            background: #545b62;
        }
        .language-btn.current {
            background: #28a745;
        }
        .success-message {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌍 Language-Aware URL System Test</h1>
        
        <div class="language-info">
            <div class="language-flag"><?= $currentLangData['flag'] ?></div>
            <div class="language-name"><?= $currentLangData['name'] ?></div>
            <div class="language-direction">Direction: <?= strtoupper($currentLangData['direction']) ?></div>
        </div>

        <?php if (isset($_GET['switched'])): ?>
        <div class="success-message">
            ✅ Language switched successfully! The URL should now show the new language prefix.
        </div>
        <?php endif; ?>

        <div class="language-switcher">
            <h3>Switch Language</h3>
            <?php foreach ($languages as $code => $lang): ?>
                <a href="<?= lang_url('test-language-urls.php?switched=1', $code) ?>" 
                   class="language-btn <?= $code === $currentLanguage ? 'current' : '' ?>">
                    <?= $lang['flag'] ?> <?= $lang['name'] ?>
                </a>
            <?php endforeach; ?>
        </div>

        <div class="test-section">
            <h3>🔗 URL Generation Examples</h3>
            <p>These URLs automatically include the current language prefix when needed:</p>
            
            <div class="url-examples">
                <div class="url-example">
                    <div class="label">Current Language:</div>
                    <div class="url"><?= $currentLanguage ?></div>
                </div>
                <div class="url-example">
                    <div class="label">Home Page:</div>
                    <div class="url"><?= lang_url('', $currentLanguage) ?></div>
                </div>
                <div class="url-example">
                    <div class="label">Quran Section:</div>
                    <div class="url"><?= lang_url('quran', $currentLanguage) ?></div>
                </div>
                <div class="url-example">
                    <div class="label">Hadith Search:</div>
                    <div class="url"><?= lang_url('hadith/search', $currentLanguage) ?></div>
                </div>
                <div class="url-example">
                    <div class="label">Wiki Create:</div>
                    <div class="url"><?= lang_url('wiki/create', $currentLanguage) ?></div>
                </div>
                <div class="url-example">
                    <div class="label">Community:</div>
                    <div class="url"><?= lang_url('community', $currentLanguage) ?></div>
                </div>
            </div>
        </div>

        <div class="test-section">
            <h3>🧭 Navigation Links</h3>
            <p>Click these links to test that language context is maintained:</p>
            
            <div class="navigation-links">
                <a href="<?= lang_url('quran', $currentLanguage) ?>" class="nav-link">📖 Quran</a>
                <a href="<?= lang_url('hadith', $currentLanguage) ?>" class="nav-link">📜 Hadith</a>
                <a href="<?= lang_url('wiki', $currentLanguage) ?>" class="nav-link">📝 Wiki</a>
                <a href="<?= lang_url('sciences', $currentLanguage) ?>" class="nav-link">🔬 Sciences</a>
                <a href="<?= lang_url('community', $currentLanguage) ?>" class="nav-link">👥 Community</a>
                <a href="<?= lang_url('docs', $currentLanguage) ?>" class="nav-link">📚 Docs</a>
            </div>
        </div>

        <div class="test-section">
            <h3>📋 Test Instructions</h3>
            <ol>
                <li><strong>Switch Language:</strong> Use the language switcher above to change languages</li>
                <li><strong>Check URL:</strong> Notice how the URL changes to include the language prefix (e.g., /ar/test-language-urls.php)</li>
                <li><strong>Navigate:</strong> Click on the navigation links to see that they maintain the language context</li>
                <li><strong>Verify Persistence:</strong> The language should stay consistent across all page visits</li>
            </ol>
            
            <h4>Expected Behavior:</h4>
            <ul>
                <li>✅ Language prefix is added to URLs for non-English languages</li>
                <li>✅ Internal links maintain language context</li>
                <li>✅ Language preference persists in session</li>
                <li>✅ RTL languages display correctly</li>
            </ul>
        </div>

        <div class="test-section">
            <h3>🔍 Current Session Data</h3>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
    </div>
</body>
</html> 