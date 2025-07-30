<?php
// Very basic test without any framework classes
echo "Basic PHP test works!";
echo "<br>Time: " . date('Y-m-d H:i:s');
echo "<br>Request URI: " . ($_SERVER['REQUEST_URI'] ?? 'not set');
echo "<br>Request Method: " . ($_SERVER['REQUEST_METHOD'] ?? 'not set');
?> 