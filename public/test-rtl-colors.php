<?php
/**
 * Test RTL Text Colors
 * This page tests if the RTL text color styling is working correctly
 * Accessible at: /test-rtl-colors.php
 */

// Set content type
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RTL Text Color Test - IslamWiki</title>
    
    <!-- Load Bismillah CSS -->
    <link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">
    
    <style>
        /* Test-specific styles */
        .test-section {
            margin: 20px 0;
            padding: 20px;
            border: 2px solid #ddd;
            border-radius: 8px;
        }
        
        .color-info {
            background: #f5f5f5;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .rtl-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #2d5016;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            z-index: 1000;
        }
        
        .current-direction {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            font-family: monospace;
        }
        
        .test-results {
            background: #e8f5e8;
            border: 2px solid #2d5016;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        
        .test-results h3 {
            color: #2d5016;
            margin-top: 0;
        }
        
        .color-preview {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 1px solid #333;
            margin-right: 10px;
            vertical-align: middle;
        }
    </style>
</head>
<body>
    <button class="rtl-toggle" onclick="toggleRTL()">Toggle RTL</button>
    <div class="current-direction" id="directionInfo">Direction: LTR</div>
    
    <div class="container" style="max-width: 800px; margin: 80px auto; padding: 20px;">
        <h1>RTL Text Color Test Page</h1>
        
        <div class="test-results">
            <h3>🎯 Test Results</h3>
            <p><strong>Current Status:</strong> <span id="testStatus">Testing RTL text colors...</span></p>
            <p><strong>CSS Loaded:</strong> <span id="cssStatus">Checking...</span></p>
            <p><strong>RTL Active:</strong> <span id="rtlStatus">No</span></p>
        </div>
        
        <div class="test-section">
            <h2>English Text (LTR Mode)</h2>
            <p>This is English text that should be readable in both LTR and RTL modes.</p>
            <p>The text colors should change when you toggle between LTR and RTL modes.</p>
        </div>
        
        <div class="test-section">
            <h2>Arabic Text (RTL Mode)</h2>
            <p dir="rtl">هذا نص عربي يجب أن يكون مقروءاً في كلا الوضعين.</p>
            <p dir="rtl">يجب أن تتغير ألوان النص عند التبديل بين الوضعين.</p>
        </div>
        
        <div class="test-section">
            <h2>Mixed Content</h2>
            <p>English text with <span dir="rtl">نص عربي</span> mixed in.</p>
            <p dir="rtl">نص عربي مع <span dir="ltr">English text</span> mixed in.</p>
        </div>
        
        <div class="test-section">
            <h2>Form Elements</h2>
            <form>
                <label>English Label:</label>
                <input type="text" value="English input text" style="width: 100%; margin: 10px 0; padding: 8px;">
                
                <label dir="rtl">تسمية عربية:</label>
                <input type="text" value="نص إدخال عربي" dir="rtl" style="width: 100%; margin: 10px 0; padding: 8px;">
            </form>
        </div>
        
        <div class="test-section">
            <h2>CSS Variables Check</h2>
            <div class="color-info">
                <strong>CSS Variables:</strong><br>
                --islamic-green: <span class="color-preview" style="background: var(--islamic-green);"></span><span style="color: var(--islamic-green);">#2d5016</span><br>
                --islamic-dark-green: <span class="color-preview" style="background: var(--islamic-dark-green);"></span><span style="color: var(--islamic-dark-green);">#1a3009</span><br>
                --islamic-white: <span class="color-preview" style="background: var(--islamic-white); border: 1px solid #ccc;"></span><span style="background: var(--islamic-white); color: #333; padding: 2px 4px;">#ffffff</span>
            </div>
        </div>
        
        <div class="test-section">
            <h2>RTL Styling Test</h2>
            <div class="color-info">
                <strong>Current Styles:</strong><br>
                <span id="rtlStyles">Check RTL styles...</span>
            </div>
        </div>
        
        <div class="test-section">
            <h2>Instructions</h2>
            <ol>
                <li>Click the "Toggle RTL" button in the top-right corner</li>
                <li>Notice how text colors change</li>
                <li>Check if Arabic text is more readable</li>
                <li>Verify that all text elements have proper contrast</li>
            </ol>
        </div>
        
        <div class="test-section">
            <h2>Expected RTL Colors</h2>
            <div class="color-info">
                <strong>When RTL is active:</strong><br>
                • Body text: <span class="color-preview" style="background: #1a3009;"></span>Dark Green (#1a3009)<br>
                • Headings: <span class="color-preview" style="background: #2d5016;"></span>Medium Green (#2d5016)<br>
                • Backgrounds: <span class="color-preview" style="background: #ffffff; border: 1px solid #ccc;"></span>White (#ffffff)
            </div>
        </div>
    </div>
    
    <script>
        function toggleRTL() {
            const html = document.documentElement;
            const directionInfo = document.getElementById('directionInfo');
            const rtlStyles = document.getElementById('rtlStyles');
            const testStatus = document.getElementById('testStatus');
            const rtlStatus = document.getElementById('rtlStatus');
            
            if (html.getAttribute('dir') === 'rtl') {
                // Switch to LTR
                html.setAttribute('dir', 'ltr');
                html.setAttribute('lang', 'en');
                document.body.classList.remove('rtl');
                document.body.classList.add('ltr');
                directionInfo.textContent = 'Direction: LTR';
                rtlStyles.innerHTML = 'Currently in LTR mode - text should be default colors';
                testStatus.textContent = 'Switched to LTR mode';
                rtlStatus.textContent = 'No';
            } else {
                // Switch to RTL
                html.setAttribute('dir', 'rtl');
                html.setAttribute('lang', 'ar');
                document.body.classList.add('rtl');
                document.body.classList.remove('ltr');
                directionInfo.textContent = 'Direction: RTL';
                rtlStyles.innerHTML = 'Currently in RTL mode - text should be Islamic green colors';
                testStatus.textContent = 'Switched to RTL mode - check text colors!';
                rtlStatus.textContent = 'Yes';
            }
            
            // Force a repaint
            document.body.offsetHeight;
            
            // Update styles after a short delay
            setTimeout(updateRTLStyles, 100);
        }
        
        function updateRTLStyles() {
            const rtlStyles = document.getElementById('rtlStyles');
            const computedStyle = getComputedStyle(document.body);
            const h1Style = getComputedStyle(document.querySelector('h1'));
            const pStyle = getComputedStyle(document.querySelector('p'));
            
            rtlStyles.innerHTML = `
                <strong>Computed Styles:</strong><br>
                Body color: <span style="color: ${computedStyle.color};">${computedStyle.color}</span><br>
                Body background: ${computedStyle.backgroundColor}<br>
                H1 color: <span style="color: ${h1Style.color};">${h1Style.color}</span><br>
                P color: <span style="color: ${pStyle.color};">${pStyle.color}</span><br>
                <br>
                <strong>RTL Detection:</strong><br>
                HTML dir: ${document.documentElement.getAttribute('dir')}<br>
                Body classes: ${document.body.className}<br>
                CSS Variables loaded: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-green') ? 'Yes' : 'No'}
            `;
        }
        
        // Check RTL styles on load
        document.addEventListener('DOMContentLoaded', function() {
            const cssStatus = document.getElementById('cssStatus');
            const testStatus = document.getElementById('testStatus');
            
            // Check if CSS is loaded
            const islamicGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-green');
            if (islamicGreen) {
                cssStatus.textContent = 'Yes - CSS Variables Available';
                testStatus.textContent = 'Ready to test RTL colors';
            } else {
                cssStatus.textContent = 'No - CSS Variables Missing';
                testStatus.textContent = 'CSS may not be loaded properly';
            }
            
            updateRTLStyles();
        });
    </script>
</body>
</html> 