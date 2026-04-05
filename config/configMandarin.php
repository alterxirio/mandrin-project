<?php
// CONFIGURATION EXAMPLE
// Rename this file to config.php and fill in your actual details.

mysqli_report(MYSQLI_REPORT_OFF);

// Detect environment
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if ($is_local) {
    // DB settings for your local machine (e.g., XAMPP)
    $con = mysqli_connect('localhost', 'root', 'YOUR_LOCAL_PASSWORD', 'database_name', 3307);
} else {
    // DB settings for your cPanel hosting
    $host = 'localhost';
    $user = 'YOUR_CPANEL_DB_USER'; 
    $pass = 'YOUR_CPANEL_DB_PASSWORD';
    $db   = 'YOUR_CPANEL_DB_NAME';

    $con = mysqli_connect($host, $user, $pass, $db);
}

if (!$con) {
    die("Connection failed: Check your config.php settings.");
}
?>