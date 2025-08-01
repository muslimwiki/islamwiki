<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Method Debug Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Method Debug Test</h1>
    
    <div class="test">
        <h3>Console Output:</h3>
        <div id="console">Loading...</div>
    </div>

    <!-- Method Debug Test -->
    <div class="test">
        <h3>Method Debug Test</h3>
        <div z-data='{"message": "", "showAlert": false}' 
             z-methods='{"showMessage": "function(msg) { this.message = msg; this.showAlert = true; }"}'>
            <button z-click="showMessage('Test message')" class="btn">
                Test Method
            </button>
            <div z-show="showAlert" class="alert alert-success">
                <span z-text="message"></span>
            </div>
        </div>
    </div>

    <script>
        // Capture ALL console output
        const consoleDiv = document.getElementById('console');
        
        function log(message) {
            console.log(message);
            consoleDiv.innerHTML += '<div style="color: blue;">LOG: ' + message + '</div>';
        }
        
        function error(message) {
            console.error(message);
            consoleDiv.innerHTML += '<div style="color: red;">ERROR: ' + message + '</div>';
        }
        
        // Override console methods to capture all output
        const originalLog = console.log;
        const originalError = console.error;
        
        console.log = function(...args) {
            originalLog.apply(console, args);
            const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : String(arg)).join(' ');
            consoleDiv.innerHTML += '<div style="color: green;">CONSOLE: ' + message + '</div>';
        };
        
        console.error = function(...args) {
            originalError.apply(console, args);
            const message = args.map(arg => typeof arg === 'object' ? JSON.stringify(arg) : String(arg)).join(' ');
            consoleDiv.innerHTML += '<div style="color: red;">CONSOLE ERROR: ' + message + '</div>';
        };
        
        log('=== ZAMZAM METHOD DEBUG TEST START ===');
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            log('ZamZam script loaded');
            
            setTimeout(() => {
                if (window.ZamZamInstance) {
                    log('ZamZamInstance found');
                    log('Components: ' + window.ZamZamInstance.components.size);
                    
                    // Test the component
                    const component = window.ZamZamInstance.components.values().next().value;
                    if (component) {
                        log('Component data: ' + JSON.stringify(component.data));
                        
                        // Check if showMessage method exists
                        if (component.data.showMessage) {
                            log('showMessage method found: ' + typeof component.data.showMessage);
                        } else {
                            log('showMessage method NOT found');
                            log('All properties: ' + Object.keys(component.data).join(', '));
                        }
                    }
                } else {
                    log('No ZamZamInstance found');
                }
            }, 3000);
        };
        script.onerror = function() {
            error('Failed to load ZamZam script');
        };
        document.head.appendChild(script);
    </script>
</body>
</html> 