<?php
include('../config/config.php');

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$payload = $_POST['questions'] ?? '';
$questions = json_decode($payload, true);

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

mysqli_begin_transaction($con);

try {
    $title = 'Homework ' . date('Y-m-d H:i:s');
    $description = 'Generated from add-work form';
    $dueDate = date('Y-m-d', strtotime('+7 days'));

    $homeworkStmt = mysqli_prepare($con, 'INSERT INTO homework (title, description, due_date) VALUES (?, ?, ?)');
    mysqli_stmt_bind_param($homeworkStmt, 'sss', $title, $description, $dueDate);

    if (!mysqli_stmt_execute($homeworkStmt)) {
        throw new Exception('Gagal simpan homework.');
    }

    $homeworkId = mysqli_insert_id($con);

    $questionStmt = mysqli_prepare(
        $con,
        'INSERT INTO questions (homework_id, type, question_text, option_a, option_b, option_c, option_d, correct_answer, audio_file, image_file)
         VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
    );

    foreach ($questions as $index => $question) {
        $type = $question['type'] ?? '';
        $questionText = trim($question['question_text'] ?? '');

        $dbType = 'mcq';
        $optionA = null;
        $optionB = null;
        $optionC = null;
        $optionD = null;
        $correctAnswer = null;
        $audioPath = null;
        $imagePath = null;

        if ($type === 'mcq-text') {
            $dbType = 'mcq';
            $choices = array_values(array_filter($question['choices'] ?? [], function ($choice) {
                return trim($choice['text'] ?? '') !== '';
            }));

            // MCQ kekal 4 atribut berasingan
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
            $correctAnswer = trim($question['correct_answer'] ?? '');

            if (!empty($question['image_key'])) {
                $imagePath = $saveUpload($question['image_key'], '../media/homework/images');
            }
        } elseif ($type === 'audio-image') {
            $dbType = 'listening';
            $choices = $question['choices'] ?? [];

            $labels = [];
            $imagePaths = [];

            foreach ($choices as $choice) {
                $label = trim($choice['label'] ?? '');
                if ($label !== '') {
                    $labels[] = $label;
                }

                if (!empty($choice['is_correct']) && $label !== '') {
                    $correctAnswer = $label;
                }

                if (!empty($choice['image_key'])) {
                    $savedImage = $saveUpload($choice['image_key'], '../media/homework/images');
                    if ($savedImage) {
                        $imagePaths[] = $savedImage;
                    }
                }
            }

            if (!empty($labels)) {
                // multiple input guna implode
                $questionText = trim(($questionText !== '' ? $questionText . ' | ' : '') . implode(',', $labels));
            }

            if (!empty($imagePaths)) {
                $imagePath = implode(',', $imagePaths);
            }

            if (!empty($question['audio_key'])) {
                $audioPath = $saveUpload($question['audio_key'], '../media/homework/audio');
            }
        } elseif ($type === 'match-image') {
            $dbType = 'picture';
            $pairs = $question['pairs'] ?? [];

            $words = [];
            $imagePaths = [];

            foreach ($pairs as $pair) {
                $word = trim($pair['word'] ?? '');
                if ($word !== '') {
                    $words[] = $word;
                }

                if (!empty($pair['image_key'])) {
                    $savedImage = $saveUpload($pair['image_key'], '../media/homework/images');
                    if ($savedImage) {
                        $imagePaths[] = $savedImage;
                    }
                }
            }

            if (!empty($words)) {
                $correctAnswer = implode(',', $words);
            }

            if (!empty($imagePaths)) {
                $imagePath = implode(',', $imagePaths);
            }
        } elseif ($type === 'drag-drop') {
            $dbType = 'rearrange';
            $words = array_values(array_filter($question['words'] ?? [], function ($word) {
                return trim($word) !== '';
            }));

            // contoh simpan: saya,suka,makan ayam
            // correct_answer: saya suka makan ayam
            $questionText = trim(($questionText !== '' ? $questionText . ' | ' : '') . implode(',', $words));
            $correctAnswer = implode(' ', $words);
        }

        if ($questionText === '') {
            $questionText = 'Question ' . ($index + 1);
        }

        mysqli_stmt_bind_param(
            $questionStmt,
            'isssssssss',
            $homeworkId,
            $dbType,
            $questionText,
            $optionA,
            $optionB,
            $optionC,
            $optionD,
            $correctAnswer,
            $audioPath,
            $imagePath
        );

        if (!mysqli_stmt_execute($questionStmt)) {
            throw new Exception('Gagal simpan soalan.');
        }
    }

    mysqli_commit($con);
    echo json_encode(['success' => true, 'message' => 'Semua soalan berjaya disimpan.']);
} catch (Exception $e) {
    mysqli_rollback($con);
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
