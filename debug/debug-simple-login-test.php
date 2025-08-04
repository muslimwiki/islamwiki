<?php
require_once __DIR__ . '/../vendor/autoload.php';

// Simple test to check if login works
echo "<h1>Simple Login Test</h1>";

// Test 1: Check if we can access the login page
echo "<h2>Test 1: Login Page Access</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "Response length: " . strlen($response) . "<br>";

// Extract CSRF token
preg_match('/name="_token" value="([^"]+)"/', $response, $matches);
$csrfToken = $matches[1] ?? 'NOT_FOUND';
echo "CSRF Token: " . substr($csrfToken, 0, 20) . "...<br>";

// Test 2: Try to login
echo "<h2>Test 2: Login Attempt</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, "username=admin&password=password&_token=$csrfToken");
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "Redirect Location: " . ($location ?: 'None') . "<br>";
echo "Response length: " . strlen($response) . "<br>";

// Test 3: Try to access dashboard
echo "<h2>Test 3: Dashboard Access</h2>";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/dashboard');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookies.txt');
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode<br>";
echo "Response length: " . strlen($response) . "<br>";

if ($httpCode == 200) {
    echo "<span style='color: green;'>✅ Dashboard accessible - Login successful!</span>";
} else {
    echo "<span style='color: red;'>❌ Dashboard not accessible - Login failed</span>";
}

echo "<br><br><a href='https://local.islam.wiki/login' target='_blank'>Open Login Page in New Tab</a>";
?> 