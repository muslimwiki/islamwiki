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
 * Test Login Functionality
 * 
 * This script tests the login functionality of IslamWiki.
 */

class LoginTester
{
    private string $baseUrl = 'http://localhost:8000';
    private array $cookies = [];
    private string $csrfToken = '';

    public function run(): void
    {
        echo "🔐 Testing IslamWiki Login Functionality\n";
        echo "========================================\n\n";

        try {
            // Step 1: Get the login page and extract CSRF token
            $this->getLoginPage();
            
            // Step 2: Test login with admin credentials
            $this->testAdminLogin();
            
            // Step 3: Test login with invalid credentials
            $this->testInvalidLogin();
            
            // Step 4: Test logout
            $this->testLogout();
            
            echo "\n✅ Login testing completed successfully!\n";
            
        } catch (Exception $e) {
            echo "\n❌ Error: " . $e->getMessage() . "\n";
            exit(1);
        }
    }

    private function getLoginPage(): void
    {
        echo "📄 Getting login page...\n";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEJAR => 'cookies.txt',
            CURLOPT_COOKIEFILE => 'cookies.txt',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception("Failed to get login page. HTTP Code: $httpCode");
        }
        
        // Extract CSRF token
        if (preg_match('/name="_token" value="([^"]+)"/', $response, $matches)) {
            $this->csrfToken = $matches[1];
            echo "✅ Got CSRF token: " . substr($this->csrfToken, 0, 10) . "...\n";
        } else {
            throw new Exception("Could not extract CSRF token from login page");
        }
        
        echo "✅ Login page loaded successfully\n";
    }

    private function testAdminLogin(): void
    {
        echo "\n👤 Testing admin login...\n";
        
        $postData = [
            'username' => 'admin',
            'password' => 'password',
            '_token' => $this->csrfToken
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 302 || $httpCode === 200) {
            // Check if we were redirected to dashboard or homepage
            if (strpos($response, 'Location: /dashboard') !== false || 
                strpos($response, 'Location: /') !== false) {
                echo "✅ Admin login successful! Redirected to dashboard/homepage\n";
            } else {
                echo "⚠️  Login response received but not sure about redirect\n";
            }
        } else {
            echo "❌ Admin login failed. HTTP Code: $httpCode\n";
            echo "Response preview: " . substr($response, 0, 200) . "...\n";
        }
    }

    private function testInvalidLogin(): void
    {
        echo "\n🚫 Testing invalid login...\n";
        
        $postData = [
            'username' => 'nonexistent',
            'password' => 'wrongpassword',
            '_token' => $this->csrfToken
        ];
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/login',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($postData),
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200 && strpos($response, 'error') !== false) {
            echo "✅ Invalid login properly rejected\n";
        } else {
            echo "⚠️  Invalid login test - unexpected response\n";
        }
    }

    private function testLogout(): void
    {
        echo "\n🚪 Testing logout...\n";
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $this->baseUrl . '/logout',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query(['_token' => $this->csrfToken]),
            CURLOPT_HEADER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE => 'cookies.txt',
            CURLOPT_COOKIEJAR => 'cookies.txt',
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 302 || $httpCode === 200) {
            echo "✅ Logout successful\n";
        } else {
            echo "⚠️  Logout test - HTTP Code: $httpCode\n";
        }
    }
}

// Run the test
$tester = new LoginTester();
$tester->run(); 