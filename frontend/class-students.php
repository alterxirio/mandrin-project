<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'pensyarah') {
    header('Location: account.php');
    exit;
}

$className = isset($_GET['class']) ? trim((string)$_GET['class']) : '';
$students = [];

if ($className !== '' && $con instanceof mysqli) {
    $studentSql = "
        SELECT
            u.id,
            u.nama,
            cs.class,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.score ELSE 0 END), 0) AS total_correct,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.incorrect ELSE 0 END), 0) AS total_incorrect,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN q.total_questions ELSE 0 END), 0) AS total_questions_submitted,
            COUNT(DISTINCT h.id) AS total_homework,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN 1 ELSE 0 END), 0) AS submitted_count
        FROM class_students cs
        INNER JOIN users u ON u.id = cs.student_id
        LEFT JOIN homework h ON h.class = cs.class
        LEFT JOIN (
            SELECT homework_id, COUNT(*) AS total_questions
            FROM questions
            GROUP BY homework_id
        ) q ON q.homework_id = h.id
        LEFT JOIN student_homework_submissions shs
            ON shs.homework_id = h.id
            AND shs.student_id = cs.student_id
        WHERE cs.class = ?
        GROUP BY u.id, u.nama, cs.class
        ORDER BY u.nama ASC
    ";

    $studentStmt = mysqli_prepare($con, $studentSql);
    if ($studentStmt) {
        mysqli_stmt_bind_param($studentStmt, 's', $className);
        mysqli_stmt_execute($studentStmt);
        $studentResult = mysqli_stmt_get_result($studentStmt);

        if ($studentResult) {
            while ($row = mysqli_fetch_assoc($studentResult)) {
                $totalQuestionsSubmitted = (int)($row['total_questions_submitted'] ?? 0);
                $totalCorrect = (int)($row['total_correct'] ?? 0);
                $averageScore = 0;

                if ($totalQuestionsSubmitted > 0) {
                    $averageScore = round(($totalCorrect / $totalQuestionsSubmitted) * 100, 1);
                }

                $totalHomework = (int)($row['total_homework'] ?? 0);
                $submittedCount = (int)($row['submitted_count'] ?? 0);

                $students[] = [
                    'id' => (int)$row['id'],
                    'name' => (string)($row['nama'] ?? '-'),
                    'class' => (string)($row['class'] ?? '-'),
                    'average_score' => $averageScore,
                    'correct_answer' => $totalCorrect,
                    'incorrect_answer' => (int)($row['total_incorrect'] ?? 0),
                    'not_submit' => max(0, $totalHomework - $submittedCount),
                ];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Senarai Pelajar</title>
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-50">
    <?php include('navbar.php'); ?>

    <main class="max-w-6xl mx-auto px-4 py-6">
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Pelajar Kelas</h1>
                    <p class="text-gray-600 mt-1"><?php echo htmlspecialchars($className !== '' ? $className : '-'); ?></p>
                </div>
                <a href="account.php" class="inline-flex items-center justify-center rounded-lg bg-gray-700 px-4 py-2 text-white text-sm font-medium hover:bg-gray-800 transition">Kembali Akaun</a>
            </div>

            <div class="mt-6 space-y-4">
                <?php if (count($students) > 0) { ?>
                    <?php foreach ($students as $student) { ?>
                        <article class="rounded-xl border border-gray-200 bg-white p-5">
                            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                                <div>
                                    <h2 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($student['name']); ?></h2>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($student['class']); ?></p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl font-bold text-red-600"><?php echo number_format((float)$student['average_score'], 1); ?>%</span>
                                    <a href="edit-student.php?id=<?php echo (int)$student['id']; ?>" class="inline-flex items-center justify-center rounded-lg bg-[#B71C1C] px-4 py-2 text-sm font-medium text-white hover:bg-[#8E1616] transition">Edit</a>
                                </div>
                            </div>

                            <div class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-3">
                                <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-3">
                                    <p class="text-xs text-emerald-700">Correct Answer</p>
                                    <p class="text-lg font-semibold text-emerald-700"><?php echo (int)$student['correct_answer']; ?></p>
                                </div>
                                <div class="rounded-lg border border-rose-200 bg-rose-50 p-3">
                                    <p class="text-xs text-rose-700">Incorrect Answer</p>
                                    <p class="text-lg font-semibold text-rose-700"><?php echo (int)$student['incorrect_answer']; ?></p>
                                </div>
                                <div class="rounded-lg border border-gray-300 bg-gray-100 p-3">
                                    <p class="text-xs text-gray-700">Not Submit</p>
                                    <p class="text-lg font-semibold text-gray-700"><?php echo (int)$student['not_submit']; ?></p>
                                </div>
                            </div>
                        </article>
                    <?php } ?>
                <?php } else { ?>
                    <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                        <p class="text-sm text-gray-600">Tiada pelajar ditemui untuk kelas ini.</p>
                    </div>
                <?php } ?>
            </div>
        </section>
    </main>
</body>
</html>
