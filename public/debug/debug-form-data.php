<?php
/**
 * Debug Form Data
 * 
 * This script shows what form data is being received by the application.
 */

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../LocalSettings.php';

echo "=== Debug Form Data ===\n\n";

// Test form submission with detailed logging
$testData = [
    'title' => 'Debug Test Page ' . date('Y-m-d H:i:s'),
    'namespace' => '',
    'content' => "# Debug Test Page\n\nThis is a test page for debugging form data.\n",
    'comment' => 'Debug test page creation',
    'content_format' => 'markdown'
];

echo "1. Sending form data:\n";
echo "   " . json_encode($testData, JSON_PRETTY_PRINT) . "\n\n";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://local.islam.wiki/pages');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($testData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
    'User-Agent: Debug Form Data Script'
]);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Capture verbose output
$verbose = fopen('php://temp', 'w+');
curl_setopt($ch, CURLOPT_STDERR, $verbose);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$location = curl_getinfo($ch, CURLINFO_REDIRECT_URL);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

echo "2. Response details:\n";
echo "   HTTP Status Code: $httpCode\n";
echo "   Content Type: $contentType\n";
if ($location) {
    echo "   Redirect Location: $location\n";
}
echo "   Response Length: " . strlen($response) . " characters\n";

// Show verbose output
rewind($verbose);
$verboseLog = stream_get_contents($verbose);
fclose($verbose);

echo "\n3. Verbose curl output:\n";
echo $verboseLog . "\n";

echo "\n4. Response preview (first 500 chars):\n";
echo substr($response, 0, 500) . "...\n";

echo "\n=== Debug Complete ===\n"; 