<?php
include('../config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Retrieve Homework Metadata from the single POST request
$homeworkTitle = $_POST['homework_name'] ?? '';
$classSelect   = $_POST['class_id'] ?? '';
$dueDate       = $_POST['due_date'] ?? null;
$description   = 'Generated from add-work form';

$payload = $_POST['questions'] ?? '';
$questions = json_decode($payload, true);

// Validation
if (empty($homeworkTitle) || empty($classSelect)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Sila isi Nama Kerja Rumah dan Pilih Kelas.']);
    exit;
}

if (!is_array($questions) || count($questions) === 0) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Tiada data soalan ditemui.']);
    exit;
}

$saveUpload = function (string $formKey, string $folder) {
    if (!isset($_FILES[$formKey]) || $_FILES[$formKey]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }

    if (!is_dir($folder)) {
        mkdir($folder, 0777, true);
    }

    $tmpName = $_FILES[$formKey]['tmp_name'];
    $originalName = basename($_FILES[$formKey]['name']);
    $safeName = preg_replace('/[^a-zA-Z0-9._-]/', '-', $originalName);
    $newName = uniqid('hw_', true) . '-' . $safeName;
    $path = $folder . '/' . $newName;

    if (!move_uploaded_file($tmpName, $path)) {
        return null;
    }

    return $path;
};

$joinAsCsv = function (array $values) {
    $filtered = array_values(array_filter(array_map(function ($value) {
        return trim((string)$value);
    }, $values), function ($value) {
        return $value !== '';
    }));

    return empty($filtered) ? null : implode(',', $filtered);
};

mysqli_begin_transaction($con);

try {
    // 1. Insert into homework table first
    $homeworkStmt = mysqli_prepare($con, 'INSERT INTO homework (title, description, class, due_date) VALUES (?, ?, ?, ?)');
    mysqli_stmt_bind_param($homeworkStmt, 'ssss', $homeworkTitle, $description, $classSelect, $dueDate);

    if (!mysqli_stmt_execute($homeworkStmt)) {
        throw new Exception('Gagal simpan maklumat kerja rumah.');
    }

    $homeworkId = mysqli_insert_id($con);

    // 2. Prepare Question Statement
    $questionStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $index => $question) {
        $type = $question['type'] ?? '';
        $questionText = trim($question['question_text'] ?? '');

        $dbType = 'mcq';
        $optionA = $optionB = $optionC = $optionD = null;
        $audioImageLabel = $correctAnswer = $audioPath = $imagePath = null;

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $choices = array_values(array_filter($question['choices'] ?? [], function ($choice) {
                return trim($choice['text'] ?? '') !== '';
            }));

            $optionA = $choices[0]['text'] ?? null;
            $optionB = $choices[1]['text'] ?? null;
            $optionC = $choices[2]['text'] ?? null;
            $optionD = $choices[3]['text'] ?? null;

            foreach ($choices as $choice) {
                if (!empty($choice['is_correct'])) {
                    $correctAnswer = $choice['text'] ?? null;
                    break;
                }
            }
        } elseif ($type === 'true-false') {
            $dbType = 'truefalse';
            $correctAnswer = $question['correct_answer'] ?? null;
            if (!empty($question['image_key'])) {
                $imagePath = $saveUpload($question['image_key'], '../media/homework/images');
            }
        } elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $choices = $question['choices'] ?? [];
            $labels = [];
            $choiceImagePaths = [];

            foreach ($choices as $choice) {
                $label = trim($choice['label'] ?? '');
                if ($label !== '') $labels[] = $label;
                if (!empty($choice['is_correct']) && $label !== '') $correctAnswer = $label;
                if (!empty($choice['image_key'])) {
                    $saved = $saveUpload($choice['image_key'], '../media/homework/images');
                    if ($saved) $choiceImagePaths[] = $saved;
                }
            }
            $audioImageLabel = $joinAsCsv($labels);
            $imagePath = $joinAsCsv($choiceImagePaths);
            if (!empty($question['audio_key'])) {
                $audioPath = $saveUpload($question['audio_key'], '../media/homework/audio');
            }
        } elseif ($type === 'match-image') {
            $dbType = 'picture';
            $pairs = array_values(array_filter($question['pairs'] ?? [], function ($pair) {
                return trim($pair['word'] ?? '') !== '';
            }));
            if (count($pairs) > 0) {
                $pairWords = array_map(fn($p) => $p['word'], $pairs);
                $correctAnswer = $joinAsCsv($pairWords);
                $pairImages = [];
                foreach ($pairs as $pair) {
                    if (!empty($pair['image_key'])) {
                        $saved = $saveUpload($pair['image_key'], '../media/homework/images');
                        if ($saved) $pairImages[] = $saved;
                    }
                }
                $imagePath = $joinAsCsv($pairImages);
            }
        } elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $words = array_values(array_filter($question['words'] ?? [], fn($w) => trim($w) !== ''));
            $correctAnswer = $joinAsCsv($words);
        }

        if ($questionText === '') {
            $questionText = 'Question ' . ($index + 1);
        }

        mysqli_stmt_bind_param(
            $questionStmt,
            'issssssssss',
            $homeworkId,
            $dbType,
            $questionText,
            $optionA,
            $optionB,
            $optionC,
            $optionD,
            $audioImageLabel,
            $correctAnswer,
            $audioPath,
            $imagePath
        );

        if (!mysqli_stmt_execute($questionStmt)) {
            throw new Exception('Gagal simpan soalan pada indeks ' . $index);
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Kerja rumah dan semua soalan berjaya disimpan.']);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>