<?php
/**
 * Test: /en Prefix Language System
 * 
 * This script demonstrates that the language switching now works correctly
 * with the new /en prefix system where English uses /en/quran instead of just /quran.
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
    'en' => ['name' => 'English', 'flag' => '🇺🇸', 'native' => 'English'],
    'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦', 'native' => 'العربية'],
    'ur' => ['name' => 'Urdu', 'flag' => '🇵🇰', 'native' => 'اردو'],
    'tr' => ['name' => 'Turkish', 'flag' => '🇹🇷', 'native' => 'Türkçe'],
    'id' => ['name' => 'Indonesian', 'flag' => '🇮🇩', 'native' => 'Bahasa Indonesia'],
    'ms' => ['name' => 'Malay', 'flag' => '🇲🇾', 'native' => 'Bahasa Melayu'],
    'fa' => ['name' => 'Persian', 'flag' => '🇮🇷', 'native' => 'فارسی'],
    'he' => ['name' => 'Hebrew', 'flag' => '🇮🇱', 'native' => 'עברית']
];

echo "<!DOCTYPE html>";
echo "<html lang='{$currentLanguage}'>";
echo "<head>";
echo "<meta charset='UTF-8'>";
echo "<meta name='viewport' content='width=device-width, initial-scale=1.0'>";
echo "<title>🌍 /en Prefix Language System Test</title>";
echo "<style>";
echo "body { font-family: Arial, sans-serif; margin: 0; padding: 20px; background: #f5f5f5; }";
echo ".container { max-width: 1200px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }";
echo "h1 { color: #2c3e50; text-align: center; margin-bottom: 30px; }";
echo ".current-language { text-align: center; margin-bottom: 30px; padding: 20px; background: #ecf0f1; border-radius: 8px; }";
echo ".language-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px; }";
echo ".language-card { padding: 20px; border: 2px solid #ddd; border-radius: 8px; text-align: center; transition: all 0.3s; }";
echo ".language-card:hover { border-color: #3498db; transform: translateY(-2px); }";
echo ".language-card.current { border-color: #27ae60; background: #d5f4e6; }";
echo ".language-flag { font-size: 2em; margin-bottom: 10px; }";
echo ".language-name { font-weight: bold; margin-bottom: 5px; }";
echo ".language-native { color: #666; font-size: 0.9em; }";
echo ".test-links { background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px; }";
echo ".test-links h3 { margin-top: 0; color: #495057; }";
echo ".test-links a { display: inline-block; margin: 5px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 5px; }";
echo ".test-links a:hover { background: #0056b3; }";
echo ".info-box { background: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin-bottom: 20px; border-radius: 4px; }";
echo "</style>";
echo "</head>";
echo "<body>";

echo "<div class='container'>";
echo "<h1>🌍 /en Prefix Language System Test</h1>";

echo "<div class='info-box'>";
echo "<strong>🎯 What's New:</strong> English now uses <code>/en/quran</code> instead of just <code>/quran</code>. ";
echo "This makes language switching symmetrical and fixes the issue where switching back to English didn't work.";
echo "</div>";

echo "<div class='current-language'>";
echo "<h2>Current Language: {$languages[$currentLanguage]['flag']} {$languages[$currentLanguage]['name']} ({$languages[$currentLanguage]['native']})</h2>";
echo "<p>Session Language: " . ($_SESSION['language'] ?? 'Not set') . "</p>";
echo "<p>Current URI: " . ($_SERVER['REQUEST_URI'] ?? 'Unknown') . "</p>";
echo "</div>";

echo "<div class='test-links'>";
echo "<h3>🧪 Test Language Switching</h3>";
echo "<p>Click these links to test the new language system:</p>";
echo "<a href='/language/switch/en?current_path=/quran'>Switch to English</a>";
echo "<a href='/language/switch/ar?current_path=/quran'>Switch to Arabic</a>";
echo "<a href='/language/switch/ur?current_path=/quran'>Switch to Urdu</a>";
echo "<a href='/language/switch/tr?current_path=/quran'>Switch to Turkish</a>";
echo "<a href='/language/switch/id?current_path=/quran'>Switch to Indonesian</a>";
echo "<a href='/language/switch/ms?current_path=/quran'>Switch to Malay</a>";
echo "<a href='/language/switch/fa?current_path=/quran'>Switch to Persian</a>";
echo "<a href='/language/switch/he?current_path=/quran'>Switch to Hebrew</a>";
echo "</div>";

echo "<div class='test-links'>";
echo "<h3>🔗 Test Direct Language URLs</h3>";
echo "<p>Test accessing content directly in different languages:</p>";
echo "<a href='/en/quran'>English Quran</a>";
echo "<a href='/ar/quran'>Arabic Quran</a>";
echo "<a href='/ur/quran'>Urdu Quran</a>";
echo "<a href='/tr/quran'>Turkish Quran</a>";
echo "<a href='/en/hadith'>English Hadith</a>";
echo "<a href='/ar/hadith'>Arabic Hadith</a>";
echo "<a href='/en/wiki'>English Wiki</a>";
echo "<a href='/ar/wiki'>Arabic Wiki</a>";
echo "</div>";

echo "<div class='language-grid'>";
foreach ($languages as $code => $lang) {
    $isCurrent = ($code === $currentLanguage) ? 'current' : '';
    echo "<div class='language-card {$isCurrent}'>";
    echo "<div class='language-flag'>{$lang['flag']}</div>";
    echo "<div class='language-name'>{$lang['name']}</div>";
    echo "<div class='language-native'>{$lang['native']}</div>";
    if ($code === $currentLanguage) {
        echo "<div style='color: #27ae60; font-weight: bold; margin-top: 10px;'>✓ Current</div>";
    }
    echo "</div>";
}
echo "</div>";

echo "<div class='info-box'>";
echo "<strong>✅ Benefits of /en Prefix:</strong>";
echo "<ul>";
echo "<li>Symmetrical language switching (English ↔ Arabic ↔ Turkish ↔ English)</li>";
echo "<li>Consistent URL patterns for all languages</li>";
echo "<li>No more issues switching back to English</li>";
echo "<li>Easier to maintain and debug</li>";
echo "<li>Better SEO and user experience</li>";
echo "</ul>";
echo "</div>";

echo "</div>";
echo "</body>";
echo "</html>";
?> 