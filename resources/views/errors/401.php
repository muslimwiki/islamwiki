<!DOCTYPE html>
<html>
<head>
    <title>401 - Authentication Required</title>
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
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            background: white;
            border: 1px solid #e1e4e8;
            border-radius: 8px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.1);
        }
        .error-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .error-icon {
            font-size: 3em;
            margin-right: 20px;
            color: #e74c3c;
        }
        h1 {
            color: #e74c3c;
            margin-top: 0;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 15px;
            font-size: 2.2em;
        }
        .error-summary {
            background: #fff5f5;
            border-left: 4px solid #e74c3c;
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
            color: #e74c3c;
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
        .auth-actions {
            display: flex;
            gap: 15px;
            margin: 20px 0;
            flex-wrap: wrap;
        }
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        .btn-primary:hover {
            background-color: #0056b3;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }
        .btn-secondary:hover {
            background-color: #545b62;
            transform: translateY(-1px);
        }
        .btn-success {
            background-color: #28a745;
            color: white;
        }
        .btn-success:hover {
            background-color: #1e7e34;
            transform: translateY(-1px);
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
        @media (max-width: 768px) {
            .container {
                padding: 15px;
                margin: 15px;
            }
            .error-meta {
                grid-template-columns: 1fr;
            }
            .auth-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="error-header">
            <div class="error-icon">🔒</div>
            <div>
                <h1>401 - Authentication Required</h1>
                <p>You need to be logged in to access this page. Please authenticate to continue.</p>
            </div>
        </div>

        <div class="error-summary">
            <h2>Error Summary</h2>
            <p><strong>Error:</strong> Authentication required to access this resource</p>
            <p><strong>Time:</strong> <?php echo date('Y-m-d H:i:s'); ?></p>
            <p><strong>Resource:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?></p>
        </div>

        <div class="suggestion-box">
            <h3>What you can do:</h3>
            <ul>
                <li><strong>Log in</strong> to your account to access this page</li>
                <li><strong>Register</strong> for a new account if you don't have one</li>
                <li><strong>Go back</strong> to the <a href="/" style="color: #1976d2; text-decoration: none;">homepage</a></li>
                <li><strong>Contact support</strong> if you believe you should have access</li>
            </ul>
        </div>

        <div class="auth-actions">
            <a href="/login" class="btn btn-primary">🔑 Log In</a>
            <a href="/register" class="btn btn-success">📝 Register</a>
            <a href="/" class="btn btn-secondary">🏠 Go Home</a>
        </div>

        <div class="error-details">
            <h2>Request Details</h2>
            <div class="error-meta">
                <dt>Requested URL:</dt>
                <dd><?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? 'Unknown'); ?></dd>
                <dt>Request Method:</dt>
                <dd><?php echo htmlspecialchars($_SERVER['REQUEST_METHOD'] ?? 'Unknown'); ?></dd>
                <dt>Referrer:</dt>
                <dd><?php echo !empty($_SERVER['HTTP_REFERER']) ? htmlspecialchars($_SERVER['HTTP_REFERER']) : 'None'; ?></dd>
                <dt>User Agent:</dt>
                <dd><?php echo htmlspecialchars($_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'); ?></dd>
                <dt>IP Address:</dt>
                <dd><?php echo htmlspecialchars($_SERVER['REMOTE_ADDR'] ?? 'Unknown'); ?></dd>
                <dt>Timestamp:</dt>
                <dd><?php echo date('Y-m-d H:i:s'); ?></dd>
            </div>
        </div>

        <?php if (getenv('APP_DEBUG') === 'true') : ?>
            <div class="debug-section">
                <h2>Debug Information</h2>
                <p><strong>Session Status:</strong> <?php echo session_status(); ?></p>
                <p><strong>Session ID:</strong> <?php echo session_id(); ?></p>
                <p><strong>PHP Version:</strong> <?php echo phpversion(); ?></p>
                <p><strong>Server Software:</strong> <?php echo htmlspecialchars($_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'); ?></p>
                
                <h3>Session Data:</h3>
                <pre><?php echo htmlspecialchars(print_r($_SESSION ?? [], true)); ?></pre>
                
                <h3>Cookies:</h3>
                <pre><?php echo htmlspecialchars(print_r($_COOKIE ?? [], true)); ?></pre>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 