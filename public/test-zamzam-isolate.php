<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Isolate Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Isolate Test</h1>
    
    <div class="test">
        <h3>Console Output:</h3>
        <div id="console">Loading...</div>
    </div>

    <div class="test">
        <h3>Test:</h3>
        <div z-data='{"test": false}'>
            <button z-click="test = !test" class="btn">Toggle Test</button>
            <div z-show="test" class="alert alert-success">This should show/hide</div>
        </div>
    </div>

    <script>
        // Simple console capture
        const consoleDiv = document.getElementById('console');
        
        function log(message) {
            console.log(message);
            consoleDiv.innerHTML += '<div style="color: blue;">LOG: ' + message + '</div>';
        }
        
        function error(message) {
            console.error(message);
            consoleDiv.innerHTML += '<div style="color: red;">ERROR: ' + message + '</div>';
        }
        
        log('=== ZAMZAM ISOLATE TEST START ===');
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            log('ZamZam script loaded');
            
            setTimeout(() => {
                if (window.ZamZamInstance) {
                    log('ZamZamInstance found');
                    log('Components: ' + window.ZamZamInstance.components.size);
                    
                    // Test the safeEval method directly
                    const component = window.ZamZamInstance.components.values().next().value;
                    if (component) {
                        log('Testing safeEval directly');
                        const result = window.ZamZamInstance.safeEval('test = !test', component.data);
                        log('Direct safeEval result: ' + result);
                        log('Component data after: ' + JSON.stringify(component.data));
                    }
                } else {
                    log('No ZamZamInstance found');
                }
            }, 2000);
        };
        script.onerror = function() {
            error('Failed to load ZamZam script');
        };
        document.head.appendChild(script);
    </script>
</body>
</html> 