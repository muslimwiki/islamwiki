<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Show Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Show Test</h1>
    
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
        
        log('=== ZAMZAM SHOW TEST START ===');
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            log('ZamZam script loaded');
            
            setTimeout(() => {
                if (window.ZamZamInstance) {
                    log('ZamZamInstance found');
                    log('Components: ' + window.ZamZamInstance.components.size);
                    
                    // Test the z-show directive directly
                    const component = window.ZamZamInstance.components.values().next().value;
                    if (component) {
                        log('Testing z-show directive directly');
                        
                        // Test with test = false
                        log('Initial test value: ' + component.data.test);
                        const showResult1 = window.ZamZamInstance.evaluateExpression('test', component.data);
                        log('Show result with test=false: ' + showResult1);
                        
                        // Set test = true
                        component.data.test = true;
                        log('Set test to true');
                        log('New test value: ' + component.data.test);
                        
                        // Test z-show again
                        const showResult2 = window.ZamZamInstance.evaluateExpression('test', component.data);
                        log('Show result with test=true: ' + showResult2);
                        
                        // Apply directives manually
                        log('Applying directives manually');
                        window.ZamZamInstance.applyDirectives(component.element, component.data);
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