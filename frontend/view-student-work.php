<?php
require_once __DIR__ . '/../config/session.php';
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    header('Location: work.php');
    exit;
}

$homeworkId = isset($_GET['homework_id']) ? (int)$_GET['homework_id'] : 0;
$studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : 0;

if ($homeworkId <= 0 || $studentId <= 0) {
    header('Location: work.php');
    exit;
}

$metaStmt = mysqli_prepare(
    $con,
    "SELECT
        h.title AS homework_title,
        h.class AS homework_class,
        u.nama AS student_name,
        shs.submitted_at,
        shs.score,
        (SELECT COUNT(*) FROM questions q WHERE q.homework_id = h.id) AS total_questions
     FROM student_homework_submissions shs
     INNER JOIN homework h ON h.id = shs.homework_id
     INNER JOIN users u ON u.id = shs.student_id
     WHERE shs.homework_id = ? AND shs.student_id = ? AND shs.status = 'submitted'
     ORDER BY shs.id DESC
     LIMIT 1"
);

mysqli_stmt_bind_param($metaStmt, 'ii', $homeworkId, $studentId);
mysqli_stmt_execute($metaStmt);
$metaResult = mysqli_stmt_get_result($metaStmt);
$meta = mysqli_fetch_assoc($metaResult);

if (!$meta) {
    header('Location: work.php');
    exit;
}

$answersStmt = mysqli_prepare(
    $con,
    "SELECT
        q.id AS question_id,
        q.type,
        q.question_text,
        q.correct_answer,
        sha.answer_text
     FROM student_homework_submissions shs
     INNER JOIN student_homework_answers sha ON sha.submission_id = shs.id
     INNER JOIN questions q ON q.id = sha.question_id
     WHERE shs.homework_id = ? AND shs.student_id = ? AND shs.status = 'submitted'
     ORDER BY q.id ASC"
);

mysqli_stmt_bind_param($answersStmt, 'ii', $homeworkId, $studentId);
mysqli_stmt_execute($answersStmt);
$answersResult = mysqli_stmt_get_result($answersStmt);

$answers = [];
while ($row = mysqli_fetch_assoc($answersResult)) {
    $answers[] = $row;
}

$totalQuestions = max(1, (int)$meta['total_questions']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student Work</title>
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('navbar.php'); ?>

<div class="max-w-5xl mx-auto px-6 py-8 space-y-6">
    <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-2">
        <h1 class="text-2xl font-semibold text-gray-800"><?php echo htmlspecialchars($meta['homework_title']); ?></h1>
        <p class="text-sm text-gray-500">Class: <?php echo htmlspecialchars($meta['homework_class']); ?></p>
        <div class="flex flex-wrap gap-2 text-xs text-gray-600">
            <span class="px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700">Student: <?php echo htmlspecialchars($meta['student_name']); ?></span>
            <span class="px-2.5 py-1 rounded-full bg-indigo-50 border border-indigo-200 text-indigo-700">Score: <?php echo (int)$meta['score']; ?>/<?php echo $totalQuestions; ?></span>
            <span class="px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200">Submitted: <?php echo htmlspecialchars((string)$meta['submitted_at']); ?></span>
        </div>
    </section>

    <section class="space-y-4">
        <?php if (empty($answers)) { ?>
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-sm text-gray-500">No answers found for this submission.</div>
        <?php } else { ?>
            <?php foreach ($answers as $index => $answer) { ?>
                <article class="bg-white rounded-xl border border-gray-200 p-5 space-y-2">
                    <p class="text-xs uppercase tracking-wide font-bold text-gray-500">Question <?php echo $index + 1; ?> · <?php echo htmlspecialchars($answer['type']); ?></p>
                    <h2 class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($answer['question_text']); ?></h2>
                    <p class="text-sm text-gray-700"><span class="font-medium text-gray-900">Student answer:</span> <?php echo nl2br(htmlspecialchars($answer['answer_text'])); ?></p>
                    <p class="text-sm text-gray-500"><span class="font-medium text-gray-700">Correct answer:</span> <?php echo htmlspecialchars((string)$answer['correct_answer']); ?></p>
                </article>
            <?php } ?>
        <?php } ?>
    </section>

    <a href="work.php" class="inline-flex items-center justify-center rounded-lg bg-gray-700 px-4 py-2 text-sm font-semibold text-white hover:bg-gray-800">Back to Homework</a>
</div>
</body>
</html>
