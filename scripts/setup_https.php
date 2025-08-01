<?php
/**
 * This file is part of IslamWiki.
 *
 * Copyright (C) 2025 IslamWiki Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

declare(strict_types=1);

/**
 * HTTPS Setup Script for IslamWiki
 * 
 * This script helps configure HTTPS for your IslamWiki installation.
 * Run this script after setting up SSL certificates.
 */

echo "🔒 IslamWiki HTTPS Setup Script\n";
echo "================================\n\n";

// Check if we're running from the correct directory
if (!file_exists(__DIR__ . '/../LocalSettings.php')) {
    echo "❌ Error: This script must be run from the scripts directory.\n";
    echo "Please run: php scripts/setup_https.php\n";
    exit(1);
}

$basePath = dirname(__DIR__);
$envFile = $basePath . '/.env';

echo "📋 Checking current configuration...\n";

// Check if .env file exists
if (file_exists($envFile)) {
    echo "✅ .env file found\n";
    $envContent = file_get_contents($envFile);
    
    // Check if APP_URL is already set to HTTPS
    if (strpos($envContent, 'APP_URL=https://') !== false) {
        echo "✅ APP_URL is already configured for HTTPS\n";
    } else {
        echo "⚠️  APP_URL needs to be updated to HTTPS\n";
        echo "   Please update your .env file to include:\n";
        echo "   APP_URL=https://local.islam.wiki\n";
    }
} else {
    echo "⚠️  No .env file found\n";
    echo "   Creating .env file with HTTPS configuration...\n";
    
    $envContent = "# IslamWiki Environment Configuration\n";
    $envContent .= "# This file contains environment-specific configuration for IslamWiki\n\n";
    $envContent .= "# Application Configuration\n";
    $envContent .= "APP_NAME=IslamWiki\n";
    $envContent .= "APP_ENV=production\n";
    $envContent .= "APP_DEBUG=false\n";
    $envContent .= "APP_URL=https://local.islam.wiki\n";
    $envContent .= "APP_TIMEZONE=UTC\n\n";
    $envContent .= "# Security Configuration\n";
    $envContent .= "SESSION_SECURE=true\n";
    $envContent .= "SESSION_SECURE_COOKIE=true\n\n";
    $envContent .= "# Database Configuration (update these with your actual values)\n";
    $envContent .= "DB_CONNECTION=mysql\n";
    $envContent .= "DB_HOST=127.0.0.1\n";
    $envContent .= "DB_PORT=3306\n";
    $envContent .= "DB_DATABASE=islamwiki\n";
    $envContent .= "DB_USERNAME=root\n";
    $envContent .= "DB_PASSWORD=\n\n";
    $envContent .= "# Generate a secure secret key\n";
    $envContent .= "APP_KEY=" . base64_encode(random_bytes(32)) . "\n";
    $envContent .= "SESSION_SECRET=" . bin2hex(random_bytes(32)) . "\n";
    $envContent .= "UPGRADE_KEY=" . bin2hex(random_bytes(32)) . "\n";
    
    file_put_contents($envFile, $envContent);
    echo "✅ .env file created with HTTPS configuration\n";
}

// Check .htaccess file
$htaccessFile = $basePath . '/public/.htaccess';
if (file_exists($htaccessFile)) {
    $htaccessContent = file_get_contents($htaccessFile);
    
    if (strpos($htaccessContent, 'RewriteCond %{HTTPS} off') !== false) {
        echo "✅ .htaccess file is configured to force HTTPS\n";
    } else {
        echo "⚠️  .htaccess file needs HTTPS redirect configuration\n";
    }
    
    if (strpos($htaccessContent, 'Strict-Transport-Security') !== false) {
        echo "✅ Security headers are configured in .htaccess\n";
    } else {
        echo "⚠️  Security headers need to be added to .htaccess\n";
    }
} else {
    echo "❌ .htaccess file not found in public directory\n";
}

// Check SSL certificate
echo "\n🔐 SSL Certificate Check:\n";
$hostname = 'local.islam.wiki';
$context = stream_context_create([
    'ssl' => [
        'capture_peer_cert' => true,
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);

$client = @stream_socket_client("ssl://{$hostname}:443", $errno, $errstr, 30, STREAM_CLIENT_CONNECT, $context);
if ($client) {
    $params = stream_context_get_params($client);
    if (isset($params['options']['ssl']['peer_certificate'])) {
        $cert = openssl_x509_parse($params['options']['ssl']['peer_certificate']);
        if ($cert) {
            echo "✅ SSL certificate found for {$hostname}\n";
            echo "   Issuer: " . $cert['issuer']['CN'] . "\n";
            echo "   Valid until: " . date('Y-m-d H:i:s', $cert['validTo_time_t']) . "\n";
        } else {
            echo "❌ SSL certificate could not be parsed\n";
        }
    } else {
        echo "❌ SSL certificate not found\n";
    }
    fclose($client);
} else {
    echo "❌ Could not connect to {$hostname}:443\n";
    echo "   Error: {$errstr} (Code: {$errno})\n";
}

// Check Apache/Nginx configuration
echo "\n🌐 Web Server Configuration:\n";
if (function_exists('apache_get_modules')) {
    $modules = apache_get_modules();
    if (in_array('mod_rewrite', $modules)) {
        echo "✅ Apache mod_rewrite is enabled\n";
    } else {
        echo "⚠️  Apache mod_rewrite is not enabled\n";
    }
    if (in_array('mod_headers', $modules)) {
        echo "✅ Apache mod_headers is enabled\n";
    } else {
        echo "⚠️  Apache mod_headers is not enabled\n";
    }
} else {
    echo "ℹ️  Apache modules check not available (not running under Apache)\n";
}

// Test HTTPS redirect
echo "\n🧪 Testing HTTPS Configuration:\n";
$testUrl = "http://{$hostname}";
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => "User-Agent: IslamWiki-HTTPS-Test/1.0\r\n",
        'timeout' => 10,
        'follow_location' => false,
    ]
]);

$response = @file_get_contents($testUrl, false, $context);
if ($response === false) {
    echo "⚠️  Could not test HTTP to HTTPS redirect\n";
} else {
    $headers = $http_response_header ?? [];
    $statusLine = $headers[0] ?? '';
    
    if (strpos($statusLine, '301') !== false || strpos($statusLine, '302') !== false) {
        echo "✅ HTTP to HTTPS redirect is working\n";
        
        // Check if redirect goes to HTTPS
        foreach ($headers as $header) {
            if (strpos($header, 'Location:') === 0) {
                $location = trim(substr($header, 9));
                if (strpos($location, 'https://') === 0) {
                    echo "✅ Redirect location is HTTPS: {$location}\n";
                } else {
                    echo "⚠️  Redirect location is not HTTPS: {$location}\n";
                }
                break;
            }
        }
    } else {
        echo "⚠️  HTTP to HTTPS redirect may not be working\n";
        echo "   Status: {$statusLine}\n";
    }
}

echo "\n📝 Next Steps:\n";
echo "1. Ensure your SSL certificate is properly installed\n";
echo "2. Configure your web server (Apache/Nginx) for HTTPS\n";
echo "3. Test the site at https://local.islam.wiki\n";
echo "4. Check that all assets load over HTTPS\n";
echo "5. Verify that cookies are secure\n";

echo "\n🔧 Manual Configuration Required:\n";
echo "- If using Apache, ensure mod_rewrite and mod_headers are enabled\n";
echo "- If using Nginx, configure SSL and redirect rules\n";
echo "- Update your DNS to point local.islam.wiki to your server\n";
echo "- Consider using Let's Encrypt for free SSL certificates\n";

echo "\n✅ HTTPS setup script completed!\n"; 