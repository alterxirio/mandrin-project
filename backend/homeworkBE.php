<?php
require_once __DIR__ . '/../config/session.php';
// 1. Prevent any HTML error leaking
error_reporting(0);
ini_set('display_errors', 0);

header('Content-Type: application/json');
include('../config/config.php');
require_once __DIR__ . '/../config/upload.php';

// Simple log function for debugging (writes to a file instead of the screen)
function debug_log($msg) {
    file_put_contents('debug.log', date('Y-m-d H:i:s') . ': ' . $msg . PHP_EOL, FILE_APPEND);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}


if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses tidak dibenarkan.']);
    exit;
}

// 2. Retrieve Data
$homeworkTitle = $_POST['homework_name'] ?? '';
$classSelect   = $_POST['class_id'] ?? '';
$dueDate       = $_POST['due_date'] ?? null;
$payload       = $_POST['questions'] ?? '[]';
$questions     = json_decode($payload, true);

if (empty($homeworkTitle) || empty($classSelect) || empty($questions)) {
    echo json_encode(['success' => false, 'message' => 'Sila lengkapkan maklumat dan soalan.']);
    exit;
}

// 3. Robust Upload Function
function saveUpload($formKey, $subFolder) {
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    $fileExtension = strtolower(pathinfo($_FILES[$formKey]['name'], PATHINFO_EXTENSION));
    if ($fileExtension === '') {
        $fileExtension = 'bin';
    }

    $newName = uniqid('hw_', true) . '.' . $fileExtension;
    return saveUploadedFile($formKey, 'media/homework/' . $subFolder, $newName);
}

// 4. Database Transaction
mysqli_begin_transaction($con);

try {
    // Insert Homework Header
    $hwStmt = mysqli_prepare($con, 'INSERT INTO homework (title, class, due_date) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($hwStmt, 'sss', $homeworkTitle, $classSelect, $dueDate);
    
    if (!mysqli_stmt_execute($hwStmt)) throw new Exception('Gagal simpan header: ' . mysqli_error($con));
    $homeworkId = mysqli_insert_id($con);

    // Prepare Question Insert
    $qStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $index => $q) {
        $type = $q['type'];
        $dbType = 'mcq';
        $optionA = $optionB = $optionC = $optionD = $audioLabel = $correct = $audioPath = $imagePath = null;
        $qText = $q['question_text'] ?: "Soalan " . ($index + 1);

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $optionA = $q['choices'][0]['text'] ?? '';
            $optionB = $q['choices'][1]['text'] ?? '';
            $optionC = $q['choices'][2]['text'] ?? '';
            $optionD = $q['choices'][3]['text'] ?? '';
            foreach ($q['choices'] as $c) {
                if (!empty($c['is_correct'])) $correct = $c['text'];
            }
        } 
        elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $labels = []; $imgPaths = [];
            foreach ($q['choices'] as $cIdx => $c) {
                $labels[] = trim((string)($c['label'] ?? ''));
                if (!empty($c['is_correct'])) $correct = $c['label'];

                $imgKey = "q_{$index}_c_{$cIdx}_img";
                $savedImg = saveUpload($imgKey, 'images');
                $imgPaths[] = $savedImg ?: '';
            }
            $audioLabel = implode(',', $labels);
            $imagePath  = implode(',', $imgPaths);
            $audioPath  = saveUpload("q_{$index}_audio", 'audio');
        }
        elseif ($type === 'match-image') {
            $dbType = 'picture';
            $words = []; $imgPaths = [];
            foreach ($q['pairs'] as $pIdx => $p) {
                $words[] = trim((string)($p['word'] ?? ''));
                $imgKey = "q_{$index}_p_{$pIdx}_img";
                $savedImg = saveUpload($imgKey, 'images');
                $imgPaths[] = $savedImg ?: '';
            }
            $correct   = implode(',', $words);
            $imagePath = implode(',', $imgPaths);
        }
        elseif ($type === 'true-false') {
            $dbType = 'truefalse';
            $correct = $q['correct_answer'];
            $imagePath = saveUpload("q_{$index}_tf_image", 'images');
        }
        elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $correct = trim((string)($q['correct_answer'] ?? ''));
            if ($correct === '') {
                $correct = implode(',', $q['words'] ?? []);
            }
        }

        mysqli_stmt_bind_param($qStmt, 'issssssssss', 
            $homeworkId, $dbType, $qText, $optionA, $optionB, $optionC, $optionD, $audioLabel, $correct, $audioPath, $imagePath
        );

        if (!mysqli_stmt_execute($qStmt)) {
            throw new Exception("Gagal simpan soalan $index: " . mysqli_stmt_error($qStmt));
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Berjaya disimpan!']);

} catch (Exception $e) {
    mysqli_rollback($con);
    
    // 1. Log the general error message
    debug_log("Error: " . $e->getMessage());
    
    // 2. Log the specific MySQL error from the connection
    debug_log("MySQL Error: " . mysqli_error($con));
    
    // 3. Log the state of the variables being inserted (optional)
    debug_log("Last attempted HW ID: " . $homeworkId);

    echo json_encode(['success' => false, 'message' => 'Server Error: ' . $e->getMessage()]);
}
?>  