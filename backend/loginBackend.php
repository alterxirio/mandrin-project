<?php
require_once __DIR__ . '/../config/session.php';
include(__DIR__ . '/../config/config.php');

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

if ($username === '' || $password === '') {
    $_SESSION['error'] = 'Please enter your username and password.';
    header('Location: ../index.php');
    exit;
}

$stmt = mysqli_prepare($con, 'SELECT * FROM users WHERE angkagiliran = ? LIMIT 1');
if (!$stmt) {
    $_SESSION['error'] = 'Login service is unavailable.';
    header('Location: ../index.php');
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $username);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$match = $result ? mysqli_fetch_assoc($result) : null;

if ($match && $password === $match['password']) {
    session_regenerate_id(true);
    $_SESSION['name'] = $match['nama'];
    $_SESSION['id'] = $match['id'];
    $_SESSION['role'] = $match['role'];
    header('Location: ../frontend/main.php');
    exit;
}

$_SESSION['error'] = 'Username or password invalid';
header('Location: ../index.php');
exit;
?>
