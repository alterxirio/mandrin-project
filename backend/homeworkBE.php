<?php
include('../config/config.php');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// 1. Retrieve Data
$homeworkTitle = $_POST['homework_name'] ?? '';
$classSelect   = $_POST['class_id'] ?? '';
$dueDate       = $_POST['due_date'] ?? null;
$payload       = $_POST['questions'] ?? '[]';
$questions     = json_decode($payload, true);

// 2. Validation
if (empty($homeworkTitle) || empty($classSelect) || empty($questions)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Sila lengkapkan maklumat dan soalan.']);
    exit;
}

// 3. Helper Functions
$saveUpload = function (string $formKey, string $folder) {
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] !== UPLOAD_ERR_OK) return null;
    if (!is_dir($folder)) mkdir($folder, 0777, true);

    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', basename($_FILES[$formKey]['name']));
    $newName = uniqid('hw_', true) . '-' . $safeName;
    $path = $folder . '/' . $newName;

    return move_uploaded_file($_FILES[$formKey]['tmp_name'], $path) ? $path : null;
};

$clip = function (?string $value, int $max) {
    if ($value === null) return null;
    return substr($value, 0, $max);
};


// Start Database Transaction
mysqli_begin_transaction($con);

try {
    // 4. Insert into Homework Table
    $hwStmt = mysqli_prepare($con, 'INSERT INTO homework (title, class, due_date) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($hwStmt, 'sss', $homeworkTitle, $classSelect, $dueDate);
    
    if (!mysqli_stmt_execute($hwStmt)) throw new Exception('Gagal simpan header kerja rumah.');
    $homeworkId = mysqli_insert_id($con);

    // 5. Prepare Question Statement
    $qStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file) 
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $index => $q) {
        $type = $q['type'] ?? '';
        $dbType = 'mcq';
        $optionA = $optionB = $optionC = $optionD = $audioLabel = $correct = $audioPath = $imagePath = null;
        $qText = $q['question_text'] ?? '';
        $qText = trim($qText) !== '' ? $qText : "Soalan " . ($index + 1);;

        // --- TYPE LOGIC ---

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $optionA = $q['choices'][0]['text'] ?? null;
            $optionB = $q['choices'][1]['text'] ?? null;
            $optionC = $q['choices'][2]['text'] ?? null;
            $optionD = $q['choices'][3]['text'] ?? null;
             foreach (($q['choices'] ?? []) as $c) {
                if (!empty($c['is_correct'])) $correct = $c['text'];
            }
        } 
        
        elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $labels = []; $imgPaths = [];
            foreach (($q['choices'] ?? []) as $c) {
                $labels[] = $c['label'] ?? '';
                if (!empty($c['is_correct'])) $correct = $c['label'] ?? null;
                if (!empty($c['image_key'])) {
                    $saved = $saveUpload($c['image_key'], '../media/homework/images');
                    $imgPaths[] = $saved ?: '';
                } else {
                    $imgPaths[] = '';
                }
            }
            $audioLabel = implode(',', $labels);
            $imagePath  = implode(',', $imgPaths);
            $audioPath  = $saveUpload($q['audio_key'] ?? '', '../media/homework/audio');
        }

        elseif ($type === 'match-image') {
            $dbType = 'picture';
            $words = []; $imgPaths = [];
            foreach (($q['pairs'] ?? []) as $p) {
                $words[] = $p['word'] ?? '';
                if (!empty($p['image_key'])) {
                    $saved = $saveUpload($p['image_key'], '../media/homework/images');
                    $imgPaths[] = $saved ?: '';
                } else {
                    $imgPaths[] = '';
                }
            }
            $correct   = implode(',', $words); // Words in order
            $imagePath = implode(',', $imgPaths); // Images in order
        }

        elseif ($type === 'true-false') {
            $dbType = 'truefalse';
            $correct = $q['correct_answer'] ?? null;
            $imagePath = $saveUpload($q['image_key'] ?? '', '../media/homework/images');
        }

        elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $words = array_values(array_filter(($q['words'] ?? []), fn($w) => trim((string)$w) !== ''));
            $correct = implode(',', $words);
        }

        // 6. Bind and Execute
        
        $qText = $clip($qText, 1000);
        $optionA = $clip($optionA, 255);
        $optionB = $clip($optionB, 255);
        $optionC = $clip($optionC, 255);
        $optionD = $clip($optionD, 255);
        $audioLabel = $clip($audioLabel, 232);
        $correct = $clip($correct, 1000);
        $audioPath = $clip($audioPath, 255);
        $imagePath = $clip($imagePath, 1000);

        mysqli_stmt_bind_param($qStmt, 'issssssssss', 
            $homeworkId, $dbType, $qText, $optionA, $optionB, $optionC, $optionD, $audioLabel, $correct, $audioPath, $imagePath
        );

        if (!mysqli_stmt_execute($qStmt)) {
            throw new Exception("Gagal simpan soalan indeks $index: " . mysqli_stmt_error($qStmt));
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Berjaya disimpan!']);

} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
