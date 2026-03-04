<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$action = $_POST['action'] ?? '';
$homeworkId = isset($_POST['homework_id']) ? (int)$_POST['homework_id'] : 0;

if ($homeworkId <= 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Homework ID tidak sah.']);
    exit;
}

function saveUpload($formKey, $subFolder) {
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $baseDir = '../media/homework/' . $subFolder;
    if (!is_dir($baseDir)) {
        mkdir($baseDir, 0777, true);
    }

    $fileExtension = pathinfo($_FILES[$formKey]['name'], PATHINFO_EXTENSION);
    $newName = uniqid('hw_', true) . '.' . $fileExtension;
    $targetPath = $baseDir . '/' . $newName;

    if (move_uploaded_file($_FILES[$formKey]['tmp_name'], $targetPath)) {
        return 'media/homework/' . $subFolder . '/' . $newName;
    }

    return null;
}

mysqli_begin_transaction($con);

try {
    $existsStmt = mysqli_prepare($con, 'SELECT id FROM homework WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($existsStmt, 'i', $homeworkId);
    mysqli_stmt_execute($existsStmt);
    $existsResult = mysqli_stmt_get_result($existsStmt);
    if (mysqli_num_rows($existsResult) === 0) {
        throw new Exception('Kerja rumah tidak ditemui.');
    }

    if ($action === 'delete') {
        $deleteAnswersStmt = mysqli_prepare(
            $con,
            'DELETE sha FROM student_homework_answers sha
             INNER JOIN student_homework_submissions shs ON shs.id = sha.submission_id
             WHERE shs.homework_id = ?'
        );
        mysqli_stmt_bind_param($deleteAnswersStmt, 'i', $homeworkId);
        mysqli_stmt_execute($deleteAnswersStmt);

        $deleteSubmissionsStmt = mysqli_prepare($con, 'DELETE FROM student_homework_submissions WHERE homework_id = ?');
        mysqli_stmt_bind_param($deleteSubmissionsStmt, 'i', $homeworkId);
        mysqli_stmt_execute($deleteSubmissionsStmt);

        $deleteQuestionsStmt = mysqli_prepare($con, 'DELETE FROM questions WHERE homework_id = ?');
        mysqli_stmt_bind_param($deleteQuestionsStmt, 'i', $homeworkId);
        mysqli_stmt_execute($deleteQuestionsStmt);

        $deleteHomeworkStmt = mysqli_prepare($con, 'DELETE FROM homework WHERE id = ?');
        mysqli_stmt_bind_param($deleteHomeworkStmt, 'i', $homeworkId);
        mysqli_stmt_execute($deleteHomeworkStmt);

        mysqli_commit($con);
        echo json_encode(['success' => true, 'message' => 'Kerja rumah berjaya dipadam.']);
        exit;
    }

    if ($action !== 'update') {
        throw new Exception('Tindakan tidak sah.');
    }

    $homeworkTitle = trim((string)($_POST['homework_name'] ?? ''));
    $classSelect = trim((string)($_POST['class_id'] ?? ''));
    $dueDate = $_POST['due_date'] ?? null;
    $payload = $_POST['questions'] ?? '[]';
    $questions = json_decode($payload, true);

    if ($homeworkTitle === '' || $classSelect === '' || !is_array($questions) || count($questions) === 0) {
        throw new Exception('Sila lengkapkan maklumat dan soalan.');
    }

    $updateHeaderStmt = mysqli_prepare($con, 'UPDATE homework SET title = ?, class = ?, due_date = ? WHERE id = ?');
    mysqli_stmt_bind_param($updateHeaderStmt, 'sssi', $homeworkTitle, $classSelect, $dueDate, $homeworkId);
    if (!mysqli_stmt_execute($updateHeaderStmt)) {
        throw new Exception('Gagal kemaskini maklumat kerja rumah.');
    }

    $deleteOldQuestions = mysqli_prepare($con, 'DELETE FROM questions WHERE homework_id = ?');
    mysqli_stmt_bind_param($deleteOldQuestions, 'i', $homeworkId);
    mysqli_stmt_execute($deleteOldQuestions);

    $insertQuestionStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $index => $q) {
        $type = $q['type'] ?? '';
        $dbType = 'mcq';
        $optionA = $optionB = $optionC = $optionD = $audioLabel = $correct = $audioPath = $imagePath = null;
        $qText = trim((string)($q['question_text'] ?? ''));
        if ($qText === '') {
            $qText = 'Soalan ' . ($index + 1);
        }

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $optionA = $q['choices'][0]['text'] ?? '';
            $optionB = $q['choices'][1]['text'] ?? '';
            $optionC = $q['choices'][2]['text'] ?? '';
            $optionD = $q['choices'][3]['text'] ?? '';
            foreach (($q['choices'] ?? []) as $c) {
                if (!empty($c['is_correct'])) {
                    $correct = $c['text'];
                }
            }
        } elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $labels = [];
            $existingImages = isset($q['existing_image_paths']) && is_array($q['existing_image_paths']) ? $q['existing_image_paths'] : [];
            $imgPaths = [];

            foreach (($q['choices'] ?? []) as $cIdx => $c) {
                $labels[] = trim((string)($c['label'] ?? ''));
                if (!empty($c['is_correct'])) {
                    $correct = $c['label'];
                }

                $imgKey = "q_{$index}_c_{$cIdx}_img";
                $savedImg = saveUpload($imgKey, 'images');
                if ($savedImg) {
                    $imgPaths[] = $savedImg;
                } else {
                    $imgPaths[] = $existingImages[$cIdx] ?? '';
                }
            }

            $audioLabel = implode(',', $labels);
            $imagePath = implode(',', $imgPaths);
            $audioPath = saveUpload("q_{$index}_audio", 'audio') ?: ($q['existing_audio_file'] ?? null);
        } elseif ($type === 'match-image') {
            $dbType = 'picture';
            $words = [];
            $existingImages = isset($q['existing_image_paths']) && is_array($q['existing_image_paths']) ? $q['existing_image_paths'] : [];
            $imgPaths = [];

            foreach (($q['pairs'] ?? []) as $pIdx => $p) {
                $words[] = trim((string)($p['word'] ?? ''));
                $imgKey = "q_{$index}_p_{$pIdx}_img";
                $savedImg = saveUpload($imgKey, 'images');
                if ($savedImg) {
                    $imgPaths[] = $savedImg;
                } else {
                    $imgPaths[] = $existingImages[$pIdx] ?? '';
                }
            }

            $correct = implode(',', $words);
            $imagePath = implode(',', $imgPaths);
        } elseif ($type === 'true-false') {
            $dbType = 'truefalse';
            $correct = $q['correct_answer'] ?? '';
            $imagePath = saveUpload("q_{$index}_tf_image", 'images') ?: ($q['existing_image_file'] ?? null);
        } elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $correct = trim((string)($q['correct_answer'] ?? ''));
            if ($correct === '') {
                $correct = implode(',', $q['words'] ?? []);
            }
        }

        mysqli_stmt_bind_param(
            $insertQuestionStmt,
            'issssssssss',
            $homeworkId,
            $dbType,
            $qText,
            $optionA,
            $optionB,
            $optionC,
            $optionD,
            $audioLabel,
            $correct,
            $audioPath,
            $imagePath
        );

        if (!mysqli_stmt_execute($insertQuestionStmt)) {
            throw new Exception('Gagal kemaskini soalan.');
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Kerja rumah berjaya dikemaskini.']);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
