<?php

// Test to see where error_log messages go
echo "<h1>Error Log Test</h1>";

error_log("TEST ERROR LOG MESSAGE - " . date('Y-m-d H:i:s'));

echo "Error log message sent. Check logs to see where it went.<br>";
echo "Current time: " . date('Y-m-d H:i:s') . "<br>";
