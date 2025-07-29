<!DOCTYPE html>
<html>
<head>
    <title>500 Internal Server Error</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #2c3e50;
            background-color: #f8f9fa;
        }
        .container {
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #d32f2f;
            margin-top: 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            font-size: 2.2em;
        }
        .error-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 3em;
            margin-right: 20px;
            color: #d32f2f;
        }
        .error-summary {
            background: #fff5f5;
            border-left: 4px solid #ff5252;
            padding: 15px 20px;
            margin: 20px 0;
            border-radius: 0 4px 4px 0;
        }
        .error-details {
            background: #f8f9fa;
            border: 1px solid #e1e4e8;
            border-radius: 6px;
            padding: 15px;
            margin: 20px 0;
        }
        .error-details h2 {
            margin-top: 0;
            color: #d32f2f;
            font-size: 1.4em;
        }
        .error-meta {
            display: grid;
            grid-template-columns: max-content 1fr;
            gap: 10px 20px;
            margin: 15px 0;
        }
        .error-meta dt {
            font-weight: bold;
            color: #555;
        }
        .error-meta dd {
            margin: 0;
            font-family: 'Courier New', Courier, monospace;
            word-break: break-all;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 6px;
            overflow-x: auto;
            font-family: 'Fira Code', 'Courier New', Courier, monospace;
            font-size: 14px;
            line-height: 1.5;
            margin: 15px 0;
            max-height: 400px;
            overflow-y: auto;
        }
        pre .line-number {
            color: #6c757d;
            display: inline-block;
            width: 2em;
            user-select: none;
            opacity: 0.5;
        }
        .code-snippet {
            margin: 20px 0;
        }
        .code-snippet pre {
            margin: 0;
            border-radius: 0 0 6px 6px;
        }
        .code-header {
            background: #e9ecef;
            padding: 8px 15px;
            border-radius: 6px 6px 0 0;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.9em;
            color: #495057;
            border: 1px solid #dee2e6;
            border-bottom: none;
        }
        .code-fragment {
            background: #fff3e0;
            padding: 2px 4px;
            border-radius: 3px;
            font-family: 'Courier New', Courier, monospace;
            font-size: 0.95em;
        }
        .suggestion-box {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px 20px;
            margin: 25px 0;
            border-radius: 0 4px 4px 0;
        }
        .suggestion-box h3 {
            margin-top: 0;
            color: #2e7d32;
            font-size: 1.2em;
        }
        .suggestion-box ul {
            margin: 10px 0 0 0;
            padding-left: 20px;
        }
        .suggestion-box li {
            margin-bottom: 8px;
        }
        .debug-section {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }
        .debug-section h2 {
            color: #6c757d;
            font-size: 1.3em;
            margin-bottom: 15px;
        }
        .debug-tabs {
            display: flex;
            border-bottom: 1px solid #dee2e6;
            margin-bottom: 15px;
        }
        .debug-tab {
            padding: 8px 15px;
            cursor: pointer;
            border: 1px solid transparent;
            border-bottom: none;
            margin-right: 5px;
            border-radius: 4px 4px 0 0;
            background: #f8f9fa;
        }
        .debug-tab.active {
            background: white;
            border-color: #dee2e6 #dee2e6 #fff;
            color: #495057;
            font-weight: 500;
        }
        .debug-content {
            display: none;
        }
        .debug-content.active {
            display: block;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        table, th, td {
            border: 1px solid #dee2e6;
        }
        th, td {
            padding: 8px 12px;
            text-align: left;
        }
        th {
            background-color: #f8f9fa;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .toggle-details {
            background: none;
            border: 1px solid #ddd;
            padding: 5px 10px;
            border-radius: 4px;
            cursor: pointer;
            color: #0d6efd;
            margin: 10px 0;
        }
        .toggle-details:hover {
            background: #f1f1f1;
        }
        .hidden {
            display: none;
        }
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 15px;
            }
            .error-meta {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-header">
            <div class="error-icon">⚠️</div>
            <div>
                <h1>500 - Internal Server Error</h1>
                <p>We're sorry, but something went wrong on our end. Our team has been notified and we're working to fix it.</p>
            </div>
        </div>

        <div class="error-summary">
            <h2>Error Summary</h2>
            <?php if (isset($exception) && $exception instanceof Throwable): ?>
                <p><strong>Error:</strong> <?php echo htmlspecialchars($exception->getMessage() ?: 'Unknown error occurred'); ?></p>
                <p><strong>Type:</strong> <?php echo get_class($exception); ?></p>
                <p><strong>File:</strong> <?php echo htmlspecialchars($exception->getFile()); ?>:<?php echo $exception->getLine(); ?></p>
                <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <?php else: ?>
                <p><strong>Error:</strong> An unknown error occurred</p>
                <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <?php endif; ?>
        </div>

        <?php if (getenv('APP_DEBUG') === 'true'): ?>
            <div class="error-details">
                <h2>Error Details</h2>
                
                <div class="error-meta">
                    <div><strong>File:</strong></div>
                    <div><?php echo htmlspecialchars($exception->getFile() ?: 'Unknown'); ?>:<?php echo $exception->getLine() ?: '?'; ?></div>
                    
                    <div><strong>Request URL:</strong></div>
                    <div><?php echo htmlspecialchars(($_SERVER['REQUEST_METHOD'] ?? 'GET') . ' ' . ($_SERVER['REQUEST_URI'] ?? '/')); ?></div>
                    
                    <div><strong>Referrer:</strong></div>
                    <div><?php echo !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'None'; ?></div>
                    
                    <div><strong>IP Address:</strong></div>
                    <div><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?></div>
                    
                    <div><strong>PHP Version:</strong></div>
                    <div><?php echo phpversion(); ?></div>
                </div>

                <?php 
                // Get the error file content if possible
                $file = $exception->getFile();
                $line = $exception->getLine();
                $contextLines = 5;
                
                if ($file && is_readable($file)) {
                    $fileContent = file($file);
                    $startLine = max(0, $line - $contextLines - 1);
                    $endLine = min(count($fileContent), $line + $contextLines);
                    $snippet = array_slice($fileContent, $startLine, $endLine - $startLine, true);
                    
                    if (!empty($snippet)) {
                        echo '<div class="code-snippet">';
                        echo '<div class="code-header">' . htmlspecialchars($file) . ' (Lines ' . ($startLine + 1) . '-' . $endLine . ')</div>';
                        echo '<pre>';
                        foreach ($snippet as $i => $codeLine) {
                            $currentLine = $startLine + $i + 1;
                            $lineClass = ($currentLine == $line) ? ' style="background: #ffeb3b30;"' : '';
                            echo '<div' . $lineClass . '><span class="line-number">' . $currentLine . '</span> ' . htmlspecialchars($codeLine) . '</div>';
                        }
                        echo '</pre></div>';
                    }
                }
                ?>

                <h3>Stack Trace</h3>
                <pre><?php 
                $trace = $exception->getTraceAsString();
                // Make file paths more readable
                $trace = preg_replace('/#\d+ /', "\n", $trace);
                echo htmlspecialchars($trace); 
                ?></pre>

                <button type="button" class="toggle-details" onclick="toggleDebugInfo()">Toggle Debug Information</button>
                
                <div id="debug-info" class="hidden">
                    <div class="debug-section">
                        <h2>Server Environment</h2>
                        <div class="debug-tabs">
                            <div class="debug-tab active" onclick="showDebugTab('server-params')">$_SERVER</div>
                            <div class="debug-tab" onclick="showDebugTab('session')">$_SESSION</div>
                            <div class="debug-tab" onclick="showDebugTab('request')">$_REQUEST</div>
                        </div>
                        
                        <div id="server-params" class="debug-content active">
                            <pre><?php 
                            $serverVars = $_SERVER;
                            // Filter out sensitive information
                            $sensitiveKeys = ['PASSWORD', 'PWD', 'SECRET', 'KEY', 'TOKEN', 'AUTH', 'COOKIE'];
                            foreach ($serverVars as $key => $value) {
                                foreach ($sensitiveKeys as $sensitive) {
                                    if (stripos($key, $sensitive) !== false) {
                                        $serverVars[$key] = '***REDACTED***';
                                        break;
                                    }
                                }
                            }
                            echo htmlspecialchars(print_r($serverVars, true)); 
                            ?></pre>
                        </div>
                        
                        <div id="session" class="debug-content">
                            <?php if (!empty($_SESSION)): ?>
                                <pre><?php 
                                $sessionData = $_SESSION;
                                // Filter out sensitive session data
                                $sensitiveSessionKeys = ['password', 'token', 'secret', 'key', 'auth'];
                                foreach ($sessionData as $key => $value) {
                                    foreach ($sensitiveSessionKeys as $sensitive) {
                                        if (stripos($key, $sensitive) !== false) {
                                            $sessionData[$key] = '***REDACTED***';
                                            break;
                                        }
                                    }
                                }
                                echo htmlspecialchars(print_r($sessionData, true)); 
                                ?></pre>
                            <?php else: ?>
                                <p>No session data available.</p>
                            <?php endif; ?>
                        </div>
                        
                        <div id="request" class="debug-content">
                            <pre><?php 
                            $requestData = $_REQUEST;
                            // Filter out sensitive request data
                            $sensitiveRequestKeys = ['password', 'pwd', 'pass', 'passwd', 'passphrase', 'secret', 'token', 'key', 'auth'];
                            foreach ($requestData as $key => $value) {
                                foreach ($sensitiveRequestKeys as $sensitive) {
                                    if (stripos($key, $sensitive) !== false) {
                                        $requestData[$key] = '***REDACTED***';
                                        break;
                                    }
                                }
                            }
                            echo htmlspecialchars(print_r($requestData, true)); 
                            ?></pre>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="suggestion-box">
                <h3>What you can do:</h3>
                <ul>
                    <li>Refresh the page - Sometimes the problem is temporary</li>
                    <li>Go back to the <a href="/" style="color: #1976d2; text-decoration: none;">homepage</a></li>
                    <li>Try again in a few minutes - We might have already fixed the issue</li>
                    <li>If the problem persists, please contact our support team with the error details above</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee; font-size: 0.9em; color: #6c757d;">
            <p>Request ID: <?php echo uniqid('req_'); ?> | Server: <?php echo gethostname() ?: 'Unknown'; ?></p>
        </div>
    </div>

    <script>
        function toggleDebugInfo() {
            const debugInfo = document.getElementById('debug-info');
            debugInfo.classList.toggle('hidden');
        }
        
        function showDebugTab(tabId) {
            // Hide all tab contents
            document.querySelectorAll('.debug-content').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Deactivate all tabs
            document.querySelectorAll('.debug-tab').forEach(tab => {
                tab.classList.remove('active');
            });
            
            // Activate the selected tab
            document.getElementById(tabId).classList.add('active');
            
            // Find and activate the clicked tab button
            const tabs = document.querySelectorAll('.debug-tab');
            for (let i = 0; i < tabs.length; i++) {
                if (tabs[i].getAttribute('onclick').includes(tabId)) {
                    tabs[i].classList.add('active');
                    break;
                }
            }
        }
    </script>
</body>
</html>
