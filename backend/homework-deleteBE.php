<?php
require_once __DIR__ . '/../config/session.php';
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

$homeworkId = isset($_POST['homework_id']) ? (int)$_POST['homework_id'] : 0;
if ($homeworkId <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID kerja rumah tidak sah.']);
    exit;
}

mysqli_begin_transaction($con);

try {
    $checkStmt = mysqli_prepare($con, 'SELECT id FROM homework WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($checkStmt, 'i', $homeworkId);
    mysqli_stmt_execute($checkStmt);
    $checkResult = mysqli_stmt_get_result($checkStmt);

    if (mysqli_num_rows($checkResult) === 0) {
        throw new Exception('Kerja rumah tidak ditemui.');
    }

    $deleteAnswers = mysqli_prepare($con, 'DELETE FROM student_homework_answers WHERE submission_id IN (SELECT id FROM student_homework_submissions WHERE homework_id = ?)');
    mysqli_stmt_bind_param($deleteAnswers, 'i', $homeworkId);
    if (!mysqli_stmt_execute($deleteAnswers)) {
        throw new Exception('Gagal memadam jawapan pelajar.');
    }

    $deleteSubmissions = mysqli_prepare($con, 'DELETE FROM student_homework_submissions WHERE homework_id = ?');
    mysqli_stmt_bind_param($deleteSubmissions, 'i', $homeworkId);
    if (!mysqli_stmt_execute($deleteSubmissions)) {
        throw new Exception('Gagal memadam rekod penghantaran.');
    }

    $deleteQuestions = mysqli_prepare($con, 'DELETE FROM questions WHERE homework_id = ?');
    mysqli_stmt_bind_param($deleteQuestions, 'i', $homeworkId);
    if (!mysqli_stmt_execute($deleteQuestions)) {
        throw new Exception('Gagal memadam soalan kerja rumah.');
    }

    $deleteHomework = mysqli_prepare($con, 'DELETE FROM homework WHERE id = ?');
    mysqli_stmt_bind_param($deleteHomework, 'i', $homeworkId);
    if (!mysqli_stmt_execute($deleteHomework)) {
        throw new Exception('Gagal memadam kerja rumah.');
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Kerja rumah berjaya dipadam.']);
} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
