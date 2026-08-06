<?php
require_once __DIR__ . '/../config/session.php';
include('../config/config.php');

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'pensyarah') {
    header('Location: ../frontend/account.php?create_student=error');
    exit;
}

$studentName = isset($_POST['student_name']) ? trim((string)$_POST['student_name']) : '';
$studentPassword = isset($_POST['student_password']) ? trim((string)$_POST['student_password']) : '';
$studentClass = isset($_POST['student_class']) ? trim((string)$_POST['student_class']) : '';

if ($studentName === '' || $studentPassword === '' || $studentClass === '') {
    header('Location: ../frontend/account.php?create_student=error');
    exit;
}

if (!($con instanceof mysqli)) {
    header('Location: ../frontend/account.php?create_student=error');
    exit;
}

$studentNameUpper = function_exists('mb_strtoupper') ? mb_strtoupper($studentName, 'UTF-8') : strtoupper($studentName);

mysqli_begin_transaction($con);

try {
    $checkDuplicateSql = "SELECT id FROM users WHERE nama = ? OR angkagiliran = ? LIMIT 1";
    $checkDuplicateStmt = mysqli_prepare($con, $checkDuplicateSql);

    if (!$checkDuplicateStmt) {
        throw new Exception('Cannot prepare duplicate statement');
    }

    mysqli_stmt_bind_param($checkDuplicateStmt, 'ss', $studentNameUpper, $studentPassword);
    mysqli_stmt_execute($checkDuplicateStmt);
    $duplicateResult = mysqli_stmt_get_result($checkDuplicateStmt);

    if ($duplicateResult && mysqli_fetch_assoc($duplicateResult)) {
        mysqli_rollback($con);
        header('Location: ../frontend/account.php?create_student=duplicate');
        exit;
    }

    $insertUserSql = "INSERT INTO users (nama, angkagiliran, password, role) VALUES (?, ?, ?, 'Pelajar')";
    $insertUserStmt = mysqli_prepare($con, $insertUserSql);

    if (!$insertUserStmt) {
        throw new Exception('Cannot prepare insert user statement');
    }

    mysqli_stmt_bind_param($insertUserStmt, 'sss', $studentNameUpper, $studentPassword, $studentPassword);
    mysqli_stmt_execute($insertUserStmt);

    $studentId = mysqli_insert_id($con);
    if ($studentId <= 0) {
        throw new Exception('Cannot capture new student id');
    }

    $insertClassSql = "INSERT INTO class_students (class, student_id) VALUES (?, ?)";
    $insertClassStmt = mysqli_prepare($con, $insertClassSql);

    if (!$insertClassStmt) {
        throw new Exception('Cannot prepare class insert statement');
    }

    mysqli_stmt_bind_param($insertClassStmt, 'si', $studentClass, $studentId);
    mysqli_stmt_execute($insertClassStmt);

    mysqli_commit($con);
    header('Location: ../frontend/account.php?create_student=success');
    exit;
} catch (Throwable $e) {
    mysqli_rollback($con);
    header('Location: ../frontend/account.php?create_student=error');
    exit;
}
