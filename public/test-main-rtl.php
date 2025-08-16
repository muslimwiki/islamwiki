<?php
/**
 * Test Main Page RTL
 * Mimics the main page structure to test RTL
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Main Page RTL - IslamWiki</title>
    
    <!-- Load Bismillah CSS -->
    <link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">
    
    <style>
        .debug-panel {
            position: fixed;
            top: 10px;
            left: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 15px;
            border-radius: 8px;
            font-family: monospace;
            font-size: 12px;
            z-index: 10000;
            max-width: 300px;
        }
        
        .test-content {
            margin-top: 100px;
            padding: 20px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }
        
        .rtl-indicator {
            position: fixed;
            top: 10px;
            right: 10px;
            background: #2d5016;
            color: white;
            padding: 10px;
            border-radius: 4px;
            font-weight: bold;
            z-index: 10000;
        }
    </style>
</head>
<body>
    <!-- Debug Panel -->
    <div class="debug-panel">
        <h4>🔍 RTL Debug</h4>
        <p><strong>HTML dir:</strong> <span id="htmlDir">ltr</span></p>
        <p><strong>Body classes:</strong> <span id="bodyClasses">none</span></p>
        <p><strong>CSS Variables:</strong> <span id="cssVars">checking...</span></p>
        <p><strong>RTL Active:</strong> <span id="rtlActive">No</span></p>
        <hr>
        <p><strong>Text Color:</strong> <span id="textColor">checking...</span></p>
        <p><strong>Background:</strong> <span id="bgColor">checking...</span></p>
    </div>
    
    <!-- RTL Indicator -->
    <div class="rtl-indicator" id="rtlIndicator">
        RTL: OFF
    </div>
    
    <!-- Test Content -->
    <div class="test-content">
        <h1>Test Main Page RTL</h1>
        
        <p>This page tests if RTL styling works on a main page structure.</p>
        
        <div class="bismillah-main">
            <div class="bismillah-container">
                <h2>Main Content Area</h2>
                <p>This content should change color when RTL is enabled.</p>
                <p>Current text color should be visible and change to dark green in RTL mode.</p>
                
                <div class="card">
                    <div class="card-header">
                        <h3>Card Header</h3>
                    </div>
                    <div class="card-body">
                        <p>Card content that should also change color in RTL mode.</p>
                        <p>All text should become dark green (#1a3009) for better readability.</p>
                    </div>
                </div>
                
                <div style="margin: 20px 0;">
                    <button onclick="toggleRTL()" style="padding: 10px 20px; background: #2d5016; color: white; border: none; border-radius: 4px; cursor: pointer;">
                        Toggle RTL Mode
                    </button>
                    <button onclick="checkStyles()" style="padding: 10px 20px; background: #666; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">
                        Check Styles
                    </button>
                </div>
                
                <div id="styleResults" style="background: #f5f5f5; padding: 15px; border-radius: 4px; margin-top: 20px; display: none;">
                    <h4>Style Results:</h4>
                    <div id="styleContent"></div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        function toggleRTL() {
            const html = document.documentElement;
            const body = document.body;
            const rtlIndicator = document.getElementById('rtlIndicator');
            
            if (html.getAttribute('dir') === 'rtl') {
                // Switch to LTR
                html.setAttribute('dir', 'ltr');
                html.setAttribute('lang', 'en');
                body.classList.remove('rtl');
                body.classList.add('ltr');
                rtlIndicator.textContent = 'RTL: OFF';
                rtlIndicator.style.background = '#666';
            } else {
                // Switch to RTL
                html.setAttribute('dir', 'rtl');
                html.setAttribute('lang', 'ar');
                body.classList.add('rtl');
                body.classList.remove('ltr');
                rtlIndicator.textContent = 'RTL: ON';
                rtlIndicator.style.background = '#d4af37';
            }
            
            updateDebugInfo();
            
            // Force repaint
            document.body.offsetHeight;
        }
        
        function updateDebugInfo() {
            const htmlDir = document.getElementById('htmlDir');
            const bodyClasses = document.getElementById('bodyClasses');
            const rtlActive = document.getElementById('rtlActive');
            const rtlIndicator = document.getElementById('rtlIndicator');
            
            const dir = document.documentElement.getAttribute('dir');
            const classes = document.body.className || 'none';
            
            htmlDir.textContent = dir;
            bodyClasses.textContent = classes;
            rtlActive.textContent = dir === 'rtl' ? 'Yes' : 'No';
            
            // Check CSS variables
            const islamicGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-green');
            const islamicDarkGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-dark-green');
            
            document.getElementById('cssVars').textContent = 
                islamicGreen && islamicDarkGreen ? 'Yes' : 'No';
            
            // Check current text color
            const bodyStyle = getComputedStyle(document.body);
            document.getElementById('textColor').textContent = bodyStyle.color;
            document.getElementById('bgColor').textContent = bodyStyle.backgroundColor;
        }
        
        function checkStyles() {
            const styleResults = document.getElementById('styleResults');
            const styleContent = document.getElementById('styleContent');
            
            const bodyStyle = getComputedStyle(document.body);
            const h1Style = getComputedStyle(document.querySelector('h1'));
            const pStyle = getComputedStyle(document.querySelector('p'));
            const cardStyle = getComputedStyle(document.querySelector('.card-body'));
            
            styleContent.innerHTML = `
                <strong>Body:</strong><br>
                Color: <span style="color: ${bodyStyle.color};">${bodyStyle.color}</span><br>
                Background: ${bodyStyle.backgroundColor}<br>
                <br>
                <strong>H1:</strong><br>
                Color: <span style="color: ${h1Style.color};">${h1Style.color}</span><br>
                <br>
                <strong>P:</strong><br>
                Color: <span style="color: ${pStyle.color};">${pStyle.color}</span><br>
                <br>
                <strong>Card Body:</strong><br>
                Color: <span style="color: ${cardStyle.color};">${cardStyle.color}</span><br>
                Background: ${cardStyle.backgroundColor}<br>
                <br>
                <strong>CSS Variables:</strong><br>
                --islamic-green: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-green')}<br>
                --islamic-dark-green: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-dark-green')}<br>
                --islamic-white: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-white')}
            `;
            
            styleResults.style.display = 'block';
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDebugInfo();
            
            // Check styles every second to see changes
            setInterval(updateDebugInfo, 1000);
        });
    </script>
</body>
</html> 