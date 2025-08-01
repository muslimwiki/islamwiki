<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ZamZam Debug Test 5 & 6</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        .test { background: #f0f0f0; padding: 10px; margin: 10px 0; border-radius: 4px; }
        .btn { background: #2563eb; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px; }
        .alert { padding: 10px; border-radius: 4px; margin: 10px 0; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
    </style>
</head>
<body>
    <h1>🕌 ZamZam Debug Test 5 & 6</h1>
    
    <div class="test">
        <h3>Console Output:</h3>
        <div id="console">Loading...</div>
    </div>

    <!-- Test 5: Conditional Classes -->
    <div class="test">
        <h3>Test 5: Conditional Classes</h3>
        <div z-data='{"isActive": false, "isError": false}'>
            <button z-click="isActive = !isActive" class="btn">Toggle Active</button>
            <button z-click="isError = !isError" class="btn">Toggle Error</button>
            <div z-class='{"alert-success": isActive, "alert-error": isError}' class="alert">
                This div changes classes based on state
            </div>
        </div>
    </div>

    <!-- Test 6: Methods -->
    <div class="test">
        <h3>Test 6: Methods</h3>
        <div z-data='{"message": "", "showAlert": false}' 
             z-methods='{"showMessage": "function(msg) { this.message = msg; this.showAlert = true; setTimeout(() => this.showAlert = false, 3000); }"}'>
            <button z-click="showMessage('Bismillah! ZamZam.js is working perfectly.')" class="btn">
                Show Islamic Message
            </button>
            <div z-show="showAlert" class="alert alert-success">
                <span z-text="message"></span>
            </div>
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
        
        log('=== ZAMZAM DEBUG TEST 5 & 6 START ===');
        
        // Load ZamZam
        const script = document.createElement('script');
        script.src = '/js/zamzam.js';
        script.onload = function() {
            log('ZamZam script loaded');
            
            setTimeout(() => {
                if (window.ZamZamInstance) {
                    log('ZamZamInstance found');
                    log('Components: ' + window.ZamZamInstance.components.size);
                    
                    // Test the components
                    window.ZamZamInstance.components.forEach((component, id) => {
                        log('Component ' + id + ':');
                        log('  Element: ' + component.element.tagName);
                        log('  Data: ' + JSON.stringify(component.data));
                        
                        // Check for methods
                        if (component.data.showMessage) {
                            log('  Method showMessage found: ' + typeof component.data.showMessage);
                        }
                        
                        // Check for z-class elements
                        const classElements = component.element.querySelectorAll('[z-class]');
                        log('  z-class elements: ' + classElements.length);
                        
                        // Check for z-methods attribute
                        const methodsAttr = component.element.getAttribute('z-methods');
                        if (methodsAttr) {
                            log('  z-methods attribute: ' + methodsAttr);
                        }
                    });
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