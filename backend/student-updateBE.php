<?php
require_once __DIR__ . '/../config/session.php';
include('../config/config.php');

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'pensyarah') {
    header('Location: ../frontend/account.php');
    exit;
}

$studentId = isset($_POST['student_id']) ? (int)$_POST['student_id'] : 0;
$studentName = isset($_POST['student_name']) ? trim((string)$_POST['student_name']) : '';
$studentClass = isset($_POST['student_class']) ? trim((string)$_POST['student_class']) : '';

$allowedPrograms = ['KPD', 'BAK', 'BPM', 'KMK', 'HBP', 'HSK'];
$classPattern = '/^2 DVM (' . implode('|', $allowedPrograms) . ') (20[2-3][0-9])$/';

if ($studentId <= 0 || $studentName === '' || $studentClass === '' || !preg_match($classPattern, $studentClass)) {
    header('Location: ../frontend/account.php');
    exit;
}

if (!($con instanceof mysqli)) {
    header('Location: ../frontend/account.php');
    exit;
}

mysqli_begin_transaction($con);

try {
    $updateUserSql = "UPDATE users SET nama = ? WHERE id = ?";
    $updateUserStmt = mysqli_prepare($con, $updateUserSql);

    if (!$updateUserStmt) {
        throw new Exception('Cannot prepare user update statement');
    }

    mysqli_stmt_bind_param($updateUserStmt, 'si', $studentName, $studentId);
    mysqli_stmt_execute($updateUserStmt);

    $deleteClassSql = "DELETE FROM class_students WHERE student_id = ?";
    $deleteClassStmt = mysqli_prepare($con, $deleteClassSql);

    if (!$deleteClassStmt) {
        throw new Exception('Cannot prepare class delete statement');
    }

    mysqli_stmt_bind_param($deleteClassStmt, 'i', $studentId);
    mysqli_stmt_execute($deleteClassStmt);

    $insertClassSql = "INSERT INTO class_students (class, student_id) VALUES (?, ?)";
    $insertClassStmt = mysqli_prepare($con, $insertClassSql);

    if (!$insertClassStmt) {
        throw new Exception('Cannot prepare class insert statement');
    }

    mysqli_stmt_bind_param($insertClassStmt, 'si', $studentClass, $studentId);
    mysqli_stmt_execute($insertClassStmt);

    mysqli_commit($con);
} catch (Throwable $e) {
    mysqli_rollback($con);
}

header('Location: ../frontend/class-students.php?class=' . urlencode($studentClass));
exit;
