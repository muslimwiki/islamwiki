<?php
/**
 * Debug RTL Functionality
 * Simple test to see if RTL is working
 */
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug RTL - IslamWiki</title>
    
    <!-- Load Bismillah CSS -->
    <link rel="stylesheet" href="/skins/Bismillah/css/bismillah.css">
    
    <style>
        .debug-info {
            background: #f0f0f0;
            border: 2px solid #333;
            padding: 20px;
            margin: 20px 0;
            font-family: monospace;
        }
        
        .rtl-test {
            background: white;
            padding: 20px;
            margin: 20px 0;
            border: 2px solid #ddd;
        }
        
        .rtl-test[dir="rtl"] {
            background: #f8f8f8;
            border-color: #2d5016;
        }
        
        .toggle-btn {
            background: #2d5016;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 10px;
        }
        
        .current-state {
            background: #333;
            color: white;
            padding: 10px;
            margin: 10px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px; margin: 20px auto; padding: 20px;">
        <h1>Debug RTL Functionality</h1>
        
        <div class="debug-info">
            <h3>🔍 Debug Information</h3>
            <p><strong>Current HTML dir:</strong> <span id="currentDir">ltr</span></p>
            <p><strong>Current body classes:</strong> <span id="currentClasses">none</span></p>
            <p><strong>CSS Variables loaded:</strong> <span id="cssVars">checking...</span></p>
            <p><strong>RTL styles applied:</strong> <span id="rtlStyles">checking...</span></p>
        </div>
        
        <div class="current-state">
            <h3>🎯 Current State</h3>
            <p id="stateInfo">Page loaded in LTR mode</p>
        </div>
        
        <div>
            <button class="toggle-btn" onclick="setLTR()">Set LTR</button>
            <button class="toggle-btn" onclick="setRTL()">Set RTL</button>
            <button class="toggle-btn" onclick="checkStyles()">Check Styles</button>
        </div>
        
        <div class="rtl-test" id="rtlTest">
            <h2>Test Content</h2>
            <p>This is English text that should change color in RTL mode.</p>
            <p dir="rtl">هذا نص عربي يجب أن يكون مقروءاً.</p>
            <p>Mixed content: <span dir="rtl">نص عربي</span> with English.</p>
        </div>
        
        <div class="debug-info">
            <h3>🎨 Style Information</h3>
            <div id="styleInfo">Click "Check Styles" to see computed styles</div>
        </div>
        
        <div class="debug-info">
            <h3>📋 Instructions</h3>
            <ol>
                <li>Click "Set RTL" to enable RTL mode</li>
                <li>Notice if text colors change</li>
                <li>Click "Check Styles" to see computed values</li>
                <li>Check browser console for any errors</li>
            </ol>
        </div>
    </div>
    
    <script>
        function setRTL() {
            document.documentElement.setAttribute('dir', 'rtl');
            document.documentElement.setAttribute('lang', 'ar');
            document.body.classList.add('rtl');
            document.body.classList.remove('ltr');
            updateDebugInfo();
        }
        
        function setLTR() {
            document.documentElement.setAttribute('dir', 'ltr');
            document.documentElement.setAttribute('lang', 'en');
            document.body.classList.add('ltr');
            document.body.classList.remove('rtl');
            updateDebugInfo();
        }
        
        function updateDebugInfo() {
            const currentDir = document.getElementById('currentDir');
            const currentClasses = document.getElementById('currentClasses');
            const stateInfo = document.getElementById('stateInfo');
            const rtlTest = document.getElementById('rtlTest');
            
            const dir = document.documentElement.getAttribute('dir');
            const classes = document.body.className || 'none';
            
            currentDir.textContent = dir;
            currentClasses.textContent = classes;
            
            if (dir === 'rtl') {
                stateInfo.textContent = 'Page is now in RTL mode - text should be dark green';
                rtlTest.setAttribute('dir', 'rtl');
            } else {
                stateInfo.textContent = 'Page is now in LTR mode - text should be default colors';
                rtlTest.setAttribute('dir', 'ltr');
            }
            
            // Check CSS variables
            const islamicGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-green');
            const islamicDarkGreen = getComputedStyle(document.documentElement).getPropertyValue('--islamic-dark-green');
            
            document.getElementById('cssVars').textContent = 
                islamicGreen && islamicDarkGreen ? 'Yes' : 'No';
        }
        
        function checkStyles() {
            const styleInfo = document.getElementById('styleInfo');
            const rtlTest = document.getElementById('rtlTest');
            
            const computedStyle = getComputedStyle(rtlTest);
            const h1Style = getComputedStyle(rtlTest.querySelector('h2'));
            const pStyle = getComputedStyle(rtlTest.querySelector('p'));
            
            styleInfo.innerHTML = `
                <strong>RTL Test Container:</strong><br>
                Background: ${computedStyle.backgroundColor}<br>
                Color: <span style="color: ${computedStyle.color};">${computedStyle.color}</span><br>
                <br>
                <strong>H2 Element:</strong><br>
                Color: <span style="color: ${h1Style.color};">${h1Style.color}</span><br>
                <br>
                <strong>P Element:</strong><br>
                Color: <span style="color: ${pStyle.color};">${pStyle.color}</span><br>
                <br>
                <strong>CSS Variables:</strong><br>
                --islamic-green: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-green')}<br>
                --islamic-dark-green: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-dark-green')}<br>
                --islamic-white: ${getComputedStyle(document.documentElement).getPropertyValue('--islamic-white')}
            `;
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateDebugInfo();
            checkStyles();
        });
    </script>
</body>
</html> 