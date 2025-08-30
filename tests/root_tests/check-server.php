<?php
// Simple server check
header('Content-Type: text/plain');
echo "Server is working!\n";
echo "PHP Version: " . phpversion() . "\n";
echo "Current Time: " . date('Y-m-d H:i:s') . "\n";
