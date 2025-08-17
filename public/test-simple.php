<?php
echo "PHP is working!";
echo "<br>Current time: " . date('Y-m-d H:i:s');
echo "<br>Routes file exists: " . (file_exists('../routes/web.php') ? 'Yes' : 'No');
echo "<br>Routes file readable: " . (is_readable('../routes/web.php') ? 'Yes' : 'No');
echo "<br>Routes file size: " . filesize('../routes/web.php') . " bytes";
?> 