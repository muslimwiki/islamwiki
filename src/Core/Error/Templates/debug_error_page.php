<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error: <?= $errorType ?? 'Unknown Error' ?></title>
    <style>
        :root { 
            --primary: #e74c3c; 
            --secondary: #f8f9fa; 
            --border: #dee2e6; 
            --text: #212529; 
            --muted: #6c757d; 
            --success: #28a745;
            --info: #17a2b8;
            --warning: #ffc107;
            --danger: #dc3545;
        }
        * { 
            margin: 0; 
            padding: 0; 
            box-sizing: border-box; 
        }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif; 
            line-height: 1.6; 
            color: var(--text); 
            background: #f8f9fa; 
            padding: 20px;
            font-size: 16px;
        }
        .container { 
            max-width: 1200px; 
            margin: 0 auto; 
        }
        .header { 
            background: var(--primary); 
            color: white; 
            padding: 20px; 
            margin-bottom: 25px; 
            border-radius: 6px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin-bottom: 10px;
            font-size: 24px;
            font-weight: 500;
        }
        .error-type { 
            display: inline-block;
            background: rgba(255,255,255,0.2); 
            padding: 4px 12px; 
            border-radius: 20px; 
            font-size: 14px; 
            font-weight: 500;
            margin-top: 5px;
        }
        .message { 
            background: white; 
            padding: 15px 20px; 
            border-left: 4px solid var(--primary); 
            margin-bottom: 25px; 
            border-radius: 0 4px 4px 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .message strong {
            color: var(--primary);
        }
        .section { 
            background: white; 
            border-radius: 6px; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.05); 
            margin-bottom: 20px;
            overflow: hidden;
        }
        .section-header { 
            background: #f8f9fa; 
            padding: 12px 20px; 
            border-bottom: 1px solid var(--border); 
            cursor: pointer; 
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s;
        }
        .section-header:after {
            content: '▼';
            font-size: 12px;
            opacity: 0.7;
            transition: transform 0.2s;
        }
        .section-header:hover { 
            background: #e9ecef; 
        }
        .section-header.active:after {
            transform: rotate(-180deg);
        }
        .section-content { 
            padding: 0; 
            display: none; 
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace; 
            font-size: 14px; 
            line-height: 1.5;
        }
        .section-content.active {
            display: block;
        }
        .source-code { 
            background: #f8f9fa; 
            border: none;
            padding: 0;
            margin: 0;
            overflow-x: auto;
        }
        .line { 
            padding: 2px 15px; 
            white-space: pre;
            display: flex;
        }
        .line.highlight { 
            background: #fff3cd; 
            position: relative;
        }
        .line.highlight:before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 4px;
            background: var(--warning);
        }
        .line-number { 
            color: var(--muted); 
            margin-right: 15px; 
            min-width: 40px;
            text-align: right;
            user-select: none;
            opacity: 0.7;
        }
        table { 
            width: 100%; 
            border-collapse: collapse;
            font-size: 14px;
        }
        th, td { 
            padding: 10px 15px; 
            text-align: left; 
            border-bottom: 1px solid var(--border); 
            vertical-align: top;
        }
        th { 
            background: #f8f9fa; 
            width: 200px; 
            font-weight: 500;
        }
        pre { 
            white-space: pre-wrap; 
            margin: 0;
            font-family: inherit;
            line-height: 1.5;
        }
        code {
            font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
            background: #f1f3f5;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 90%;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 12px;
            font-weight: 500;
            line-height: 1.5;
            text-align: center;
            white-space: nowrap;
            vertical-align: baseline;
            border-radius: 4px;
        }
        .badge-primary { background: var(--primary); color: white; }
        .badge-success { background: var(--success); color: white; }
        .badge-warning { background: var(--warning); color: #212529; }
        .badge-danger { background: var(--danger); color: white; }
        .badge-info { background: var(--info); color: white; }
        
        @media (max-width: 768px) {
            th, td { 
                display: block; 
                width: 100%; 
                padding: 8px 0; 
            }
            th { 
                background: transparent; 
                padding-top: 12px;
                padding-bottom: 4px;
                border-bottom: none;
                font-weight: 600;
            }
            td {
                padding-top: 0;
                padding-bottom: 12px;
            }
            tr:last-child td {
                border-bottom: 1px solid var(--border);
            }
            .section-header {
                padding: 12px 15px;
            }
        }
        
        /* Animation for section toggling */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-5px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* Better scrollbars for code blocks */
        ::-webkit-scrollbar {
            height: 8px;
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1><?= $errorMessage ?? 'An error occurred' ?></h1>
            <span class="error-type"><?= $errorType ?? 'Error' ?></span>
        </div>
        
        <div class="message">
            <strong>In</strong> <?= $errorFile ?? 'unknown file' ?> <strong>on line</strong> <?= $errorLine ?? '0' ?>
        </div>
        
        <?php if (!empty($source)) : ?>
        <div class="section">
            <div class="section-header" onclick="toggleSection('source-code')">
                Source Code
            </div>
            <div class="section-content" id="source-code">
                <div class="source-code"><?= $source ?></div>
            </div>
        </div>
        <?php endif; ?>
        
        <div class="section">
            <div class="section-header" onclick="toggleSection('stack-trace')">
                Stack Trace
            </div>
            <div class="section-content" id="stack-trace">
                <pre><?= htmlspecialchars($errorTrace, ENT_QUOTES, 'UTF-8') ?></pre>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header" onclick="toggleSection('server-info')">
                Server Information
            </div>
            <div class="section-content" id="server-info">
                <table>
                    <?php foreach ($serverInfo as $key => $value) : ?>
                    <tr>
                        <th><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></th>
                        <td><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
        
        <div class="section">
            <div class="section-header" onclick="toggleSection('request-info')">
                Request Information
            </div>
            <div class="section-content" id="request-info">
                <table>
                    <?php foreach ($requestInfo as $key => $value) : ?>
                    <tr>
                        <th><?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8') ?></th>
                        <td><?= htmlspecialchars($value, ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <script>
        function toggleSection(id) {
            const content = document.getElementById(id);
            content.style.display = content.style.display === 'block' ? 'none' : 'block';
        }
        
        // Show first section by default
        document.addEventListener('DOMContentLoaded', function() {
            const firstSection = document.querySelector('.section-content');
            if (firstSection) firstSection.style.display = 'block';
        });
    </script>
</body>
</html>
