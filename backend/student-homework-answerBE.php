<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

header('Content-Type: application/json');
include('../config/config.php');


if (!isset($_SESSION['id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Pelajar') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$rawInput = file_get_contents('php://input');
$data = json_decode($rawInput, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Format data tidak sah.']);
    exit;
}

$homeworkId = isset($data['homework_id']) ? (int)$data['homework_id'] : 0;
$studentId  = (int)$_SESSION['id'];
$answers    = $data['answers'] ?? [];

if ($homeworkId <= 0 || $studentId <= 0 || empty($answers)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Maklumat penghantaran tidak lengkap.']);
    exit;
}

mysqli_begin_transaction($con);

try {

    // ===============================
    // CHECK HOMEWORK EXISTS
    // ===============================
    $checkHomework = mysqli_prepare($con, "SELECT id FROM homework WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkHomework, 'i', $homeworkId);
    mysqli_stmt_execute($checkHomework);
    $homeworkResult = mysqli_stmt_get_result($checkHomework);

    if (mysqli_num_rows($homeworkResult) === 0) {
        throw new Exception("Kerja rumah tidak ditemui.");
    }

    // ===============================
    // CHECK STUDENT EXISTS
    // ===============================
    $checkStudent = mysqli_prepare($con, "SELECT id FROM users WHERE id = ? LIMIT 1");
    mysqli_stmt_bind_param($checkStudent, 'i', $studentId);
    mysqli_stmt_execute($checkStudent);
    $studentResult = mysqli_stmt_get_result($checkStudent);

    if (mysqli_num_rows($studentResult) === 0) {
        throw new Exception("Pelajar tidak sah.");
    }

    // ===============================
    // INSERT / UPDATE SUBMISSION
    // IMPORTANT: Make sure UNIQUE(homework_id, student_id) exists
    // ===============================
    $status = "submitted";

    $submissionStmt = mysqli_prepare(
        $con,
        "INSERT INTO student_homework_submissions (homework_id, student_id, status, submitted_at)
         VALUES (?, ?, ?, NOW())
         ON DUPLICATE KEY UPDATE 
         status = VALUES(status),
         submitted_at = NOW(),
         updated_at = NOW()"
    );

    mysqli_stmt_bind_param($submissionStmt, 'iis', $homeworkId, $studentId, $status);

    if (!mysqli_stmt_execute($submissionStmt)) {
        throw new Exception("Gagal menyimpan submission.");
    }

    $submissionId = mysqli_insert_id($con);

    if ($submissionId == 0) {
        $fetchSubmission = mysqli_prepare(
            $con,
            "SELECT id FROM student_homework_submissions 
             WHERE homework_id = ? AND student_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($fetchSubmission, 'ii', $homeworkId, $studentId);
        mysqli_stmt_execute($fetchSubmission);
        $result = mysqli_stmt_get_result($fetchSubmission);
        $row = mysqli_fetch_assoc($result);
        $submissionId = (int)$row['id'];
    }

    if ($submissionId <= 0) {
        throw new Exception("Submission ID gagal diperoleh.");
    }

    // ===============================
    // DELETE OLD ANSWERS
    // ===============================
    $deleteOld = mysqli_prepare(
        $con,
        "DELETE FROM student_homework_answers WHERE submission_id = ?"
    );
    mysqli_stmt_bind_param($deleteOld, 'i', $submissionId);
    mysqli_stmt_execute($deleteOld);

    // ===============================
    // PREPARE INSERT ANSWER
    // ===============================
    $insertAnswerStmt = mysqli_prepare(
        $con,
        "INSERT INTO student_homework_answers (submission_id, question_id, answer_text)
         VALUES (?, ?, ?)"
    );

    $totalScore = 0;
    $totalIncorrect = 0; // NEW: Initialize incorrect counter

    // ===============================
    // LOOP EACH ANSWER
    // ===============================
    foreach ($answers as $entry) {
        $questionId = (int)($entry['question_id'] ?? 0);
        $answerText = trim((string)($entry['answer_text'] ?? ''));

        if ($questionId <= 0 || $answerText === '') {
            throw new Exception("Jawapan tidak lengkap.");
        }

        // Get correct answer
        $questionStmt = mysqli_prepare(
            $con,
            "SELECT correct_answer FROM questions 
             WHERE id = ? AND homework_id = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($questionStmt, 'ii', $questionId, $homeworkId);
        mysqli_stmt_execute($questionStmt);
        $questionResult = mysqli_stmt_get_result($questionStmt);

        if (mysqli_num_rows($questionResult) === 0) {
            throw new Exception("Soalan tidak sah.");
        }

        $questionRow = mysqli_fetch_assoc($questionResult);
        $correctAnswer = trim($questionRow['correct_answer']);

        // ===============================
        // AUTO MARKING (case insensitive)
        // ===============================
        if (strtolower($answerText) === strtolower($correctAnswer)) {
            $totalScore++;
        } else {
            $totalIncorrect++; // NEW: Increment if the answer is wrong
        }

        // Save student answer (unchanged)
        $insertAnswerStmt = mysqli_prepare(
            $con,
            "INSERT INTO student_homework_answers (submission_id, question_id, answer_text)
             VALUES (?, ?, ?)"
        );
        mysqli_stmt_bind_param($insertAnswerStmt, 'iis', $submissionId, $questionId, $answerText);
        mysqli_stmt_execute($insertAnswerStmt);
    }

    // ===============================
    // UPDATE SCORE & INCORRECT COUNT
    // ===============================
    $updateScoreStmt = mysqli_prepare(
        $con,
        "UPDATE student_homework_submissions SET score = ?, incorrect = ? WHERE id = ?"
    );
    // Updated bind_param to include the second 'i' for $totalIncorrect
    mysqli_stmt_bind_param($updateScoreStmt, 'iii', $totalScore, $totalIncorrect, $submissionId);
    mysqli_stmt_execute($updateScoreStmt);

    mysqli_commit($con);

    echo json_encode([
        'success' => true,
        'message' => 'Jawapan berjaya dihantar.',
        'score'   => $totalScore,
        'incorrect' => $totalIncorrect // Added to response for debugging
    ]);

} catch (Exception $e) {

    mysqli_rollback($con);

    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}