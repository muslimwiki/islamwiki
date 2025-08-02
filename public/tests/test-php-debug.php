<?php
echo 'PHP IS RUNNING FROM REAL DISK: ' . __FILE__;
echo '<br>PHP Version: ' . phpversion();
echo '<br>Current working directory: ' . getcwd();
echo '<br>Document root: ' . $_SERVER['DOCUMENT_ROOT'] ?? 'not set'; 