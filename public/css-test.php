<?php
/**
 * CSS Loading Test
 * Simple test to see if CSS is loading
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSS Loading Test - IslamWiki</title>
    
    <!-- Test CSS loading -->
    <link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">
    
    <style>
        .test-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            font-family: Arial, sans-serif;
        }
        
        .css-status {
            background: #f0f0f0;
            border: 2px solid #333;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        
        .css-test {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        
        .test-button {
            background: #2d5016;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin: 10px;
        }
        
        .test-button:hover {
            background: #1a3009;
        }
        
        .debug-info {
            background: #333;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="test-container">
        <h1>CSS Loading Test</h1>
        
        <div class="css-status">
            <h3>🔍 CSS Status Check</h3>
            <p><strong>CSS File:</strong> <code>/skins/Bismillah/css/bismillah.css</code></p>
            <p><strong>CSS Variables Available:</strong> <span id="cssVars">Checking...</span></p>
            <p><strong>CSS File Size:</strong> <span id="cssSize">Checking...</span></p>
            <p><strong>CSS Loaded:</strong> <span id="cssLoaded">Checking...</span></p>
        </div>
        
        <div class="css-test">
            <h3>🎨 CSS Variable Test</h3>
            <p>If CSS is loaded, these should show Islamic green colors:</p>
            
            <div style="margin: 20px 0;">
                <span style="color: var(--islamic-green); font-weight: bold;">Islamic Green Text</span><br>
                <span style="color: var(--islamic-dark-green); font-weight: bold;">Islamic Dark Green Text</span><br>
                <span style="background: var(--islamic-white); color: #333; padding: 5px; border: 1px solid #ccc;">Islamic White Background</span>
            </div>
            
            <p><strong>Expected Colors:</strong></p>
            <ul>
                <li>Islamic Green: #2d5016</li>
                <li>Islamic Dark Green: #1a3009</li>
                <li>Islamic White: #ffffff</li>
            </ul>
        </div>
        
        <div class="css-test">
            <h3>🔄 RTL Test</h3>
            <p>Click the button below to test RTL mode:</p>
            
            <button class="test-button" onclick="testRTL()">Test RTL Mode</button>
            <button class="test-button" onclick="checkCSS()">Check CSS Status</button>
            
            <div id="rtlResults" style="margin-top: 20px; display: none;">
                <h4>RTL Test Results:</h4>
                <div id="rtlContent"></div>
            </div>
        </div>
        
        <div class="debug-info">
            <h4>🐛 Debug Information</h4>
            <div id="debugContent">Click "Check CSS Status" to see debug info</div>
        </div>
        
        <div class="css-test">
            <h3>📋 Instructions</h3>
            <ol>
                <li>Check if CSS variables are showing colors above</li>
                <li>Click "Check CSS Status" to see detailed info</li>
                <li>Click "Test RTL Mode" to see if RTL styling works</li>
                <li>Look for the red border and "🚨 RTL MODE IS ACTIVE! 🚨" message</li>
            </ol>
        </div>
    </div>
    
    <script>
        function checkCSS() {
            const cssVars = document.getElementById('cssVars');
            const cssSize = document.getElementById('cssSize');
            const cssLoaded = document.getElementById('cssLoaded');
            const debugContent = document.getElementById('debugContent');
            
            // Check CSS variables
            const islamicGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-green');
            const islamicDarkGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-dark-green');
            const islamicWhite = getComputedStyle(document.documentElement).getPropertyValue('--islamic-white');
            
            cssVars.textContent = islamicGreen && islamicDarkGreen && islamicWhite ? 'Yes' : 'No';
            
            // Check CSS file size
            const cssLinks = document.querySelectorAll('link[rel="stylesheet"]');
            let cssFileSize = 'Unknown';
            cssLinks.forEach(link => {
                if (link.href.includes('bismillah.css')) {
                    cssFileSize = 'Found in DOM';
                }
            });
            cssSize.textContent = cssFileSize;
            
            // Check if CSS is loaded
            cssLoaded.textContent = islamicGreen ? 'Yes' : 'No';
            
            // Debug info
            debugContent.innerHTML = `
                <strong>CSS Variables:</strong><br>
                --islamic-green: ${islamicGreen || 'NOT FOUND'}<br>
                --islamic-dark-green: ${islamicDarkGreen || 'NOT FOUND'}<br>
                --islamic-white: ${islamicWhite || 'NOT FOUND'}<br>
                <br>
                <strong>CSS Links:</strong><br>
                ${Array.from(cssLinks).map(link => link.href).join('<br>')}<br>
                <br>
                <strong>Current Styles:</strong><br>
                Body color: ${getComputedStyle(document.body).color}<br>
                Body background: ${getComputedStyle(document.body).backgroundColor}<br>
                <br>
                <strong>HTML Attributes:</strong><br>
                dir: ${document.documentElement.getAttribute('dir')}<br>
                lang: ${document.documentElement.getAttribute('lang')}
            `;
        }
        
        function testRTL() {
            const rtlResults = document.getElementById('rtlResults');
            const rtlContent = document.getElementById('rtlContent');
            
            // Set RTL mode
            document.documentElement.setAttribute('dir', 'rtl');
            document.documentElement.setAttribute('lang', 'ar');
            document.body.classList.add('rtl');
            
            // Wait a moment for styles to apply
            setTimeout(() => {
                const bodyStyle = getComputedStyle(document.body);
                const htmlDir = document.documentElement.getAttribute('dir');
                
                rtlContent.innerHTML = `
                    <strong>RTL Status:</strong><br>
                    HTML dir: ${htmlDir}<br>
                    Body classes: ${document.body.className}<br>
                    <br>
                    <strong>Applied Styles:</strong><br>
                    Body color: <span style="color: ${bodyStyle.color};">${bodyStyle.color}</span><br>
                    Body background: ${bodyStyle.backgroundColor}<br>
                    <br>
                    <strong>Expected Results:</strong><br>
                    • Red border around page: ${htmlDir === 'rtl' ? 'Should be visible' : 'NOT WORKING'}<br>
                    • Red background tint: ${htmlDir === 'rtl' ? 'Should be visible' : 'NOT WORKING'}<br>
                    • "🚨 RTL MODE IS ACTIVE! 🚨" message: ${htmlDir === 'rtl' ? 'Should be visible' : 'NOT WORKING'}<br>
                    • Text color: Should be dark green (#1a3009)
                `;
                
                rtlResults.style.display = 'block';
            }, 100);
        }
        
        // Check CSS on load
        document.addEventListener('DOMContentLoaded', function() {
            checkCSS();
        });
    </script>
</body>
</html> 