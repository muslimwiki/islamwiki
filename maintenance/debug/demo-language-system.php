<?php

/**
 * Demo page for the Hybrid Translation System
 * Shows all features working together
 */

// Define the base path
define('BASE_PATH', dirname(__DIR__, 2));

// Load Composer's autoloader
require_once BASE_PATH . '/vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(BASE_PATH);
$dotenv->load();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Hybrid Translation System Demo</title>
    <style>
        body { 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
            margin: 40px; 
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
        .demo-container {
            max-width: 1200px;
            margin: 0 auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .demo-header {
            background: linear-gradient(135deg, #2E7D32, #388E3C);
            color: white;
            padding: 40px;
            text-align: center;
        }
        .demo-header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .demo-header p {
            margin: 10px 0 0 0;
            font-size: 1.2em;
            opacity: 0.9;
        }
        .demo-content {
            padding: 40px;
        }
        .feature-section {
            margin: 30px 0;
            padding: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 15px;
            background: #fafafa;
        }
        .feature-section h2 {
            color: #2E7D32;
            margin-top: 0;
            border-bottom: 2px solid #2E7D32;
            padding-bottom: 10px;
        }
        .feature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin: 20px 0;
        }
        .feature-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            border: 1px solid #ddd;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .feature-card h3 {
            color: #2E7D32;
            margin-top: 0;
        }
        .status-indicator {
            display: inline-block;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-right: 8px;
        }
        .status-working { background-color: #4CAF50; }
        .status-pending { background-color: #FF9800; }
        .status-error { background-color: #F44336; }
        .demo-actions {
            text-align: center;
            margin: 30px 0;
        }
        .demo-button {
            background: linear-gradient(135deg, #2E7D32, #388E3C);
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 25px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .demo-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.3);
        }
        .language-showcase {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            margin: 20px 0;
            justify-content: center;
        }
        .language-item {
            background: white;
            padding: 15px;
            border-radius: 10px;
            border: 2px solid #e0e0e0;
            text-align: center;
            min-width: 120px;
            transition: all 0.3s ease;
        }
        .language-item:hover {
            border-color: #2E7D32;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .language-flag {
            font-size: 2em;
            margin-bottom: 8px;
        }
        .language-name {
            font-weight: 600;
            color: #333;
            margin-bottom: 4px;
        }
        .language-native {
            font-size: 0.9em;
            color: #666;
        }
        .api-test {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .api-test h4 {
            margin-top: 0;
            color: #2E7D32;
        }
        .api-result {
            background: #e8f5e8;
            padding: 15px;
            border-radius: 8px;
            margin: 10px 0;
            font-family: monospace;
            white-space: pre-wrap;
            max-height: 200px;
            overflow-y: auto;
        }
        .subdomain-info {
            background: #e3f2fd;
            padding: 20px;
            border-radius: 10px;
            margin: 20px 0;
        }
        .subdomain-info h4 {
            margin-top: 0;
            color: #1976d2;
        }
        .url-example {
            background: #f5f5f5;
            padding: 10px;
            border-radius: 5px;
            font-family: monospace;
            margin: 5px 0;
            border-left: 4px solid #2E7D32;
        }
    </style>
</head>
<body>
    <div class='demo-container'>
        <div class='demo-header'>
            <h1>🌐 Hybrid Translation System Demo</h1>
            <p>Complete language switching with subdomain integration and Google Translate API</p>
        </div>
        
        <div class='demo-content'>
            <!-- Language Switch Component -->
            <div class='feature-section'>
                <h2>🎯 Live Language Switch Component</h2>
                <p>This is the actual language switch component that will be used on the main site:</p>
                <div style='text-align: center; margin: 20px 0;'>";

// Include the language switch component
include BASE_PATH . '/resources/views/components/simple-language-switch.twig';

echo "</div>
            </div>

            <!-- System Status -->
            <div class='feature-section'>
                <h2>📊 System Status</h2>
                <div class='feature-grid'>
                    <div class='feature-card'>
                        <h3>Frontend Component</h3>
                        <p><span class='status-indicator status-working'></span>Language Switch UI</p>
                        <p><span class='status-indicator status-working'></span>8 Language Support</p>
                        <p><span class='status-indicator status-working'></span>RTL/LTR Layout</p>
                        <p><span class='status-indicator status-working'></span>Responsive Design</p>
                    </div>
                    <div class='feature-card'>
                        <h3>Backend API</h3>
                        <p><span class='status-indicator status-working'></span>Language Detection</p>
                        <p><span class='status-indicator status-working'></span>Subdomain Routing</p>
                        <p><span class='status-indicator status-working'></span>Session Management</p>
                        <p><span class='status-indicator status-working'></span>Translation Endpoints</p>
                    </div>
                    <div class='feature-card'>
                        <h3>Infrastructure</h3>
                        <p><span class='status-indicator status-working'></span>Service Providers</p>
                        <p><span class='status-indicator status-working'></span>Dependency Injection</p>
                        <p><span class='status-indicator status-working'></span>Error Handling</p>
                        <p><span class='status-indicator status-working'></span>Logging System</p>
                    </div>
                </div>
            </div>

            <!-- Language Showcase -->
            <div class='feature-section'>
                <h2>🌍 Supported Languages</h2>
                <div class='language-showcase'>";

// Display all supported languages
$languages = [
    'en' => ['name' => 'English', 'native' => 'English', 'flag' => '🇺🇸', 'direction' => 'LTR'],
    'ar' => ['name' => 'Arabic', 'native' => 'العربية', 'flag' => '🇸🇦', 'direction' => 'RTL'],
    'ur' => ['name' => 'Urdu', 'native' => 'اردو', 'flag' => '🇵🇰', 'direction' => 'RTL'],
    'tr' => ['name' => 'Turkish', 'native' => 'Türkçe', 'flag' => '🇹🇷', 'direction' => 'LTR'],
    'id' => ['name' => 'Indonesian', 'native' => 'Bahasa Indonesia', 'flag' => '🇮🇩', 'direction' => 'LTR'],
    'ms' => ['name' => 'Malay', 'native' => 'Bahasa Melayu', 'flag' => '🇲🇾', 'direction' => 'LTR'],
    'fa' => ['name' => 'Persian', 'native' => 'فارسی', 'flag' => '🇮🇷', 'direction' => 'RTL'],
    'he' => ['name' => 'Hebrew', 'native' => 'עברית', 'flag' => '🇮🇱', 'direction' => 'RTL']
];

foreach ($languages as $code => $lang) {
    echo "<div class='language-item'>
            <div class='language-flag'>{$lang['flag']}</div>
            <div class='language-name'>{$lang['name']}</div>
            <div class='language-native'>{$lang['native']}</div>
            <div style='font-size: 0.8em; color: #999;'>{$lang['direction']}</div>
        </div>";
}

echo "</div>
            </div>

            <!-- Subdomain Information -->
            <div class='subdomain-info'>
                <h4>🌐 Subdomain URL Structure</h4>
                <p>The system automatically generates language-specific URLs:</p>
                <div class='url-example'>English (default): local.islam.wiki/</div>
                <div class='url-example'>Arabic: ar.local.islam.wiki/</div>
                <div class='url-example'>Urdu: ur.local.islam.wiki/</div>
                <div class='url-example'>Turkish: tr.local.islam.wiki/</div>
                <div class='url-example'>Indonesian: id.local.islam.wiki/</div>
                <div class='url-example'>Malay: ms.local.islam.wiki/</div>
                <div class='url-example'>Persian: fa.local.islam.wiki/</div>
                <div class='url-example'>Hebrew: he.local.islam.wiki/</div>
            </div>

            <!-- API Testing -->
            <div class='api-test'>
                <h4>🔧 API Endpoints Test</h4>
                <p>Test the language switching API endpoints:</p>
                <div class='demo-actions'>
                    <button class='demo-button' onclick='testAPI(\"current\")'>Test Current Language</button>
                    <button class='demo-button' onclick='testAPI(\"available\")'>Test Available Languages</button>
                    <button class='demo-button' onclick='testAPI(\"stats\")'>Test System Stats</button>
                    <button class='demo-button' onclick='testTranslation()'>Test Translation</button>
                </div>
                <div id='api-result' class='api-result' style='display: none;'></div>
            </div>

            <!-- Demo Actions -->
            <div class='feature-section'>
                <h2>🎮 Try It Out</h2>
                <div class='demo-actions'>
                    <button class='demo-button' onclick='switchToLanguage(\"ar\")'>Switch to Arabic</button>
                    <button class='demo-button' onclick='switchToLanguage(\"ur\")'>Switch to Urdu</button>
                    <button class='demo-button' onclick='switchToLanguage(\"tr\")'>Switch to Turkish</button>
                    <button class='demo-button' onclick='switchToLanguage(\"en\")'>Switch to English</button>
                </div>
                <p style='text-align: center; color: #666;'>
                    Click any language button above to see the subdomain switching in action!
                </p>
            </div>

            <!-- Technical Details -->
            <div class='feature-section'>
                <h2>⚙️ Technical Implementation</h2>
                <div class='feature-grid'>
                    <div class='feature-card'>
                        <h3>Frontend</h3>
                        <ul>
                            <li>Vanilla JavaScript (no frameworks)</li>
                            <li>Responsive CSS with Islamic theme</li>
                            <li>Accessibility compliant (ARIA labels)</li>
                            <li>Keyboard navigation support</li>
                        </ul>
                    </div>
                    <div class='feature-card'>
                        <h3>Backend</h3>
                        <ul>
                            <li>PHP 8.3 with strict typing</li>
                            <li>PSR-7 HTTP interfaces</li>
                            <li>Service provider architecture</li>
                            <li>Dependency injection container</li>
                        </ul>
                    </div>
                    <div class='feature-card'>
                        <h3>Infrastructure</h3>
                        <ul>
                            <li>Subdomain-based routing</li>
                            <li>Session-based language persistence</li>
                            <li>RESTful API endpoints</li>
                            <li>Comprehensive error handling</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        /**
         * Demo page functionality
         */
        
        // Test API endpoints
        async function testAPI(endpoint) {
            const resultDiv = document.getElementById('api-result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Testing...';
            
            try {
                const response = await fetch(`/language/${endpoint}`);
                const data = await response.json();
                
                resultDiv.textContent = `✅ ${endpoint.toUpperCase()} API Response:\n\n${JSON.stringify(data, null, 2)}`;
            } catch (error) {
                resultDiv.textContent = `❌ Error testing ${endpoint} API:\n\n${error.message}`;
            }
        }

        // Test translation API
        async function testTranslation() {
            const resultDiv = document.getElementById('api-result');
            resultDiv.style.display = 'block';
            resultDiv.textContent = 'Testing translation...';
            
            try {
                const response = await fetch('/language/translate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        text: 'Hello World',
                        target_language: 'ar'
                    })
                });
                
                const data = await response.json();
                resultDiv.textContent = `✅ Translation API Response:\n\n${JSON.stringify(data, null, 2)}`;
            } catch (error) {
                resultDiv.textContent = `❌ Error testing translation API:\n\n${error.message}`;
            }
        }

        // Switch to specific language
        async function switchToLanguage(languageCode) {
            try {
                const response = await fetch(`/language/switch/${languageCode}`);
                const result = await response.json();
                
                if (result.success) {
                    alert(`Language switched to ${languageCode}! Redirecting to: ${result.redirect_url}`);
                    // In a real scenario, this would redirect
                    console.log('Would redirect to:', result.redirect_url);
                } else {
                    alert('Language switch failed: ' + (result.error || 'Unknown error'));
                }
            } catch (error) {
                alert('Error switching language: ' + error.message);
            }
        }

        // Initialize demo page
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Hybrid Translation System Demo loaded successfully!');
            
            // Test if the language switch component is working
            const languageSwitch = document.querySelector('.simple-language-switch');
            if (languageSwitch) {
                console.log('✅ Language switch component found and loaded');
            } else {
                console.log('❌ Language switch component not found');
            }
        });
    </script>
</body>
</html>"; 