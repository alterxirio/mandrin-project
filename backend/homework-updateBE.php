<?php
session_start();
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include('../config/config.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak dibenarkan.']);
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

function splitCsvKeepingIndex(?string $value): array {
    if ($value === null || trim($value) === '') {
        return [];
    }

    return array_map('trim', explode(',', $value));
}

$homeworkId    = isset($_POST['homework_id']) ? (int)$_POST['homework_id'] : 0;
$homeworkTitle = $_POST['homework_name'] ?? '';
$classSelect   = $_POST['class_id'] ?? '';
$dueDate       = $_POST['due_date'] ?? null;
$payload       = $_POST['questions'] ?? '[]';
$questions     = json_decode($payload, true);

if ($homeworkId <= 0 || empty($homeworkTitle) || empty($classSelect) || empty($questions)) {
    echo json_encode(['success' => false, 'message' => 'Sila lengkapkan maklumat kerja rumah dan soalan.']);
    exit;
}

mysqli_begin_transaction($con);

try {
    $checkHomework = mysqli_prepare($con, 'SELECT id FROM homework WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($checkHomework, 'i', $homeworkId);
    mysqli_stmt_execute($checkHomework);
    $checkHomeworkResult = mysqli_stmt_get_result($checkHomework);

    if (mysqli_num_rows($checkHomeworkResult) === 0) {
        throw new Exception('Kerja rumah tidak ditemui.');
    }

    $hwStmt = mysqli_prepare($con, 'UPDATE homework SET title = ?, class = ?, due_date = ? WHERE id = ?');
    mysqli_stmt_bind_param($hwStmt, 'sssi', $homeworkTitle, $classSelect, $dueDate, $homeworkId);
    if (!mysqli_stmt_execute($hwStmt)) {
        throw new Exception('Gagal kemas kini maklumat kerja rumah.');
    }

    $insertStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    $updateStmt = mysqli_prepare(
        $con,
        'UPDATE questions
         SET type = ?, question_text = ?, option_a = ?, option_b = ?, option_c = ?, option_d = ?, audioImage_label = ?, correct_answer = ?, audio_file = ?, image_file = ?
         WHERE id = ? AND homework_id = ?'
    );

    foreach ($questions as $index => $q) {
        $questionId = isset($q['id']) ? (int)$q['id'] : 0;
        $type = $q['type'] ?? '';
        $dbType = 'mcq';

        $optionA = $optionB = $optionC = $optionD = $audioLabel = $correct = $audioPath = $imagePath = null;
        $qText = trim((string)($q['question_text'] ?? ''));
        if ($qText === '') {
            $qText = 'Soalan ' . ($index + 1);
        }

        $existingQuestion = null;
        if ($questionId > 0) {
            $existingStmt = mysqli_prepare($con, 'SELECT * FROM questions WHERE id = ? AND homework_id = ? LIMIT 1');
            mysqli_stmt_bind_param($existingStmt, 'ii', $questionId, $homeworkId);
            mysqli_stmt_execute($existingStmt);
            $existingResult = mysqli_stmt_get_result($existingStmt);
            $existingQuestion = mysqli_fetch_assoc($existingResult) ?: null;
        }

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $optionA = $q['choices'][0]['text'] ?? '';
            $optionB = $q['choices'][1]['text'] ?? '';
            $optionC = $q['choices'][2]['text'] ?? '';
            $optionD = $q['choices'][3]['text'] ?? '';
            foreach (($q['choices'] ?? []) as $c) {
                if (!empty($c['is_correct'])) {
                    $correct = $c['text'] ?? '';
                }
            }
        } elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $labels = [];
            $imgPaths = [];
            $existingImages = splitCsvKeepingIndex($existingQuestion['image_file'] ?? null);

            foreach (($q['choices'] ?? []) as $cIdx => $c) {
                $labels[] = trim((string)($c['label'] ?? ''));
                if (!empty($c['is_correct'])) {
                    $correct = $c['label'] ?? '';
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
            $imagePath  = implode(',', $imgPaths);
            $newAudioPath = saveUpload("q_{$index}_audio", 'audio');
            $audioPath = $newAudioPath ?: ($existingQuestion['audio_file'] ?? null);
        } elseif ($type === 'match-image') {
            $dbType = 'picture';
            $words = [];
            $imgPaths = [];
            $existingImages = splitCsvKeepingIndex($existingQuestion['image_file'] ?? null);

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

            $correct   = implode(',', $words);
            $imagePath = implode(',', $imgPaths);
        } elseif ($type === 'true-false') {
            $dbType = 'truefalse';
            $correct = $q['correct_answer'] ?? '';
            $newImagePath = saveUpload("q_{$index}_tf_image", 'images');
            $imagePath = $newImagePath ?: ($existingQuestion['image_file'] ?? null);
        } elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $correct = trim((string)($q['correct_answer'] ?? ''));
            if ($correct === '') {
                $correct = implode(',', $q['words'] ?? []);
            }
        }

        if ($questionId > 0 && $existingQuestion) {
            mysqli_stmt_bind_param(
                $updateStmt,
                'ssssssssssii',
                $dbType,
                $qText,
                $optionA,
                $optionB,
                $optionC,
                $optionD,
                $audioLabel,
                $correct,
                $audioPath,
                $imagePath,
                $questionId,
                $homeworkId
            );
            if (!mysqli_stmt_execute($updateStmt)) {
                throw new Exception('Gagal mengemas kini soalan.');
            }
        } else {
            mysqli_stmt_bind_param(
                $insertStmt,
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
            if (!mysqli_stmt_execute($insertStmt)) {
                throw new Exception('Gagal menambah soalan baharu.');
            }
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Kerja rumah berjaya dikemas kini.']);
} catch (Exception $e) {
    mysqli_rollback($con);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
