<?php
mysqli_report(MYSQLI_REPORT_OFF);

// 1. Detect if we are on Localhost or Online
$is_local = in_array($_SERVER['REMOTE_ADDR'], ['127.0.0.1', '::1']);

if ($is_local) {
    // --- LOCAL SETTINGS (Your PC) ---
    // You mentioned port 3307 for local
    $con = mysqli_connect('localhost', 'root', '', 'mandarin', 3307);
} else {
    // --- ONLINE SETTINGS (cPanel) ---
    // cPanel username: imran_mandarinAdmin
    // Password: Your password
    // DB Name: (Usually same as username or imran_mandarin)
    
    $host = 'localhost';
    $user = 'imran_mandarinAdmin'; 
    $pass = 'Aqifimran1257';
    $db   = 'imran_mandarin'; // Double check this name in cPanel!

    $con = mysqli_connect($host, $user, $pass, $db);
}

// Check if connection worked
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}
?>