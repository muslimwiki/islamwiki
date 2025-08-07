<!DOCTYPE html>
<html>
<head>
    <title>404 Page Not Found</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            color: #333;
            background-color: #f9f9f9;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            color: #d32f2f;
            margin-top: 0;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        .error-details {
            background: #f8f8f8;
            border-left: 4px solid #d32f2f;
            padding: 15px;
            margin: 20px 0;
        }
        .error-details p {
            margin: 5px 0;
        }
        pre {
            background: #2d2d2d;
            color: #f8f8f2;
            padding: 15px;
            border-radius: 4px;
            overflow-x: auto;
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
        }
        .debug-info {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }
        .suggestion {
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            padding: 15px;
            margin: 20px 0;
        }
        .suggestion h3 {
            margin-top: 0;
            color: #2e7d32;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>404 - Page Not Found</h1>
        
        <p>The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>
        
        <div class="error-details">
            <p><strong>Requested URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?></p>
            <p><strong>Request Method:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'Unknown'); ?></p>
            <p><strong>Referrer:</strong> <?php echo !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'None'; ?></p>
            <p><strong>Timestamp:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
        </div>

        <?php if (getenv('APP_DEBUG') === 'true') : ?>
            <div class="debug-info">
                <h2>Debug Information:</h2>
                <p><strong>Error Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server Software:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></p>
                <p><strong>Server Name:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_NAME'] ?? 'Unknown'); ?></p>
                
                <?php if (isset($exception) && $exception instanceof Throwable) : ?>
                    <h3>Exception Details:</h3>
                    <p><strong>Message:</strong> <?php echo htmlspecialchars($exception->getMessage()); ?></p>
                    <p><strong>File:</strong> <?php echo htmlspecialchars($exception->getFile()); ?>:<?php echo $exception->getLine(); ?></p>
                    <h4>Stack Trace:</h4>
                    <pre><?php echo htmlspecialchars($exception->getTraceAsString()); ?></pre>
                <?php endif; ?>
                
                <h3>Server Environment:</h3>
                <pre><?php
                $serverVars = $_SERVER;
                // Filter out sensitive information
                $sensitiveKeys = ['PASSWORD', 'PWD', 'SECRET', 'KEY', 'TOKEN', 'AUTH'];
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
        <?php else : ?>
            <div class="suggestion">
                <h3>Suggestions:</h3>
                <ul>
                    <li>Check the URL for any typos</li>
                    <li>Go back to the <a href="/">homepage</a></li>
                    <li>Use the search function to find what you're looking for</li>
                    <li>Contact support if you believe this is an error</li>
                </ul>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
