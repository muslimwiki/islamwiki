<?php
// Simple test that bypasses all complex application logic
header('Content-Type: text/html');
echo '<!DOCTYPE html>';
echo '<html>';
echo '<head><title>Test</title></head>';
echo '<body>';
echo '<h1>Bypass test works!</h1>';
echo '<p>Request URI: ' . $_SERVER['REQUEST_URI'] . '</p>';
echo '<p>Script Name: ' . $_SERVER['SCRIPT_NAME'] . '</p>';
echo '</body>';
echo '</html>';
exit;
?> 