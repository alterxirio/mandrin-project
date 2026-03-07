<?php
session_start();
include('../config/config.php');

$studentStats = [
    'average_score' => 0,
    'correct_answer' => 0,
    'incorrect_answer' => 0,
    'not_submit' => 0,
    'total_homework' => 0,
    'has_data' => false,
];

$scoreTextClass = 'text-green-600';
$studentId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
$isStudent = isset($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'pelajar';
$upcomingHomework = null;

if ($isStudent && $studentId > 0 && $con instanceof mysqli) {
    $statsSql = "
        SELECT
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.score ELSE 0 END), 0) AS total_correct,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.incorrect ELSE 0 END), 0) AS total_incorrect,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN q.total_questions ELSE 0 END), 0) AS total_questions_submitted,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN 1 ELSE 0 END), 0) AS submitted_count,
            COUNT(h.id) AS total_homework
        FROM class_students cs
        INNER JOIN homework h ON h.class = cs.class
        LEFT JOIN (
            SELECT homework_id, COUNT(*) AS total_questions
            FROM questions
            GROUP BY homework_id
        ) q ON q.homework_id = h.id
        LEFT JOIN student_homework_submissions shs
            ON shs.homework_id = h.id
            AND shs.student_id = cs.student_id
        WHERE cs.student_id = ?
    ";

    $statsStmt = mysqli_prepare($con, $statsSql);
    if ($statsStmt) {
        mysqli_stmt_bind_param($statsStmt, 'i', $studentId);
        mysqli_stmt_execute($statsStmt);
        $statsResult = mysqli_stmt_get_result($statsStmt);
        $statsRow = $statsResult ? mysqli_fetch_assoc($statsResult) : null;

        if ($statsRow) {
            $totalCorrect = (int)($statsRow['total_correct'] ?? 0);
            $totalIncorrect = (int)($statsRow['total_incorrect'] ?? 0);
            $totalQuestionsSubmitted = (int)($statsRow['total_questions_submitted'] ?? 0);
            $submittedCount = (int)($statsRow['submitted_count'] ?? 0);
            $totalHomework = (int)($statsRow['total_homework'] ?? 0);

            $averageScore = 0;
            if ($totalQuestionsSubmitted > 0) {
                $averageScore = round(($totalCorrect / $totalQuestionsSubmitted) * 100, 1);
            }

            $studentStats = [
                'average_score' => $averageScore,
                'correct_answer' => $totalCorrect,
                'incorrect_answer' => $totalIncorrect,
                'not_submit' => max(0, $totalHomework - $submittedCount),
                'total_homework' => $totalHomework,
                'has_data' => $totalHomework > 0,
            ];
        }
    }

    $upcomingSql = "
        SELECT h.id, h.title, h.due_date
        FROM class_students cs
        INNER JOIN homework h ON h.class = cs.class
        LEFT JOIN student_homework_submissions shs
            ON shs.homework_id = h.id
            AND shs.student_id = cs.student_id
        WHERE cs.student_id = ?
          AND h.due_date >= NOW()
          AND LOWER(COALESCE(shs.status, '')) <> 'submitted'
        ORDER BY h.due_date ASC, h.id ASC
        LIMIT 1
    ";

    $upcomingStmt = mysqli_prepare($con, $upcomingSql);
    if ($upcomingStmt) {
        mysqli_stmt_bind_param($upcomingStmt, 'i', $studentId);
        mysqli_stmt_execute($upcomingStmt);
        $upcomingResult = mysqli_stmt_get_result($upcomingStmt);
        $upcomingHomework = $upcomingResult ? mysqli_fetch_assoc($upcomingResult) : null;
    }
}

$averageValue = (float)$studentStats['average_score'];
if ($averageValue <= 49) {
    $scoreTextClass = 'text-red-600';
} elseif ($averageValue <= 60) {
    $scoreTextClass = 'text-yellow-500';
} else {
    $scoreTextClass = 'text-green-600';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akaun</title>
    <?php include("header.php")?>
</head>
<body class="bg-gray-50">
    <?php include("navbar.php")?>

    <main class="max-w-5xl mx-auto px-4 py-6">
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Akaun Saya</h1>
                    <p class="text-gray-600 mt-1">Maklumat ringkas akaun anda.</p>
                </div>
                <a href="../frontend/edit-profile.php" class="inline-flex items-center justify-center rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">
                    Sunting Profil
                </a>
            </div>

            <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Nama</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($_SESSION['name'] ?? 'Pengguna'); ?></p>
                </div>
                <div class="rounded-lg border border-gray-200 p-4 bg-gray-50">
                    <p class="text-xs uppercase tracking-wide text-gray-500">Peranan</p>
                    <p class="text-lg font-semibold text-gray-900 mt-1"><?php echo htmlspecialchars($_SESSION['role'] ?? '-'); ?></p>
                </div>
            </div>

            <?php if ($isStudent) { ?>
            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-xl border border-gray-200 p-6 bg-white">
                    <p class="text-sm font-medium text-gray-500">Average Homework Score</p>
                    <p class="mt-3 text-5xl font-bold <?php echo $scoreTextClass; ?>">
                        <?php echo number_format((float)$studentStats['average_score'], 1); ?>%
                    </p>
                    <p class="mt-2 text-sm text-gray-500">Berdasarkan markah tugasan yang telah dihantar.</p>
                </div>

                <div class="rounded-xl border border-gray-200 p-6 bg-white">
                    <p class="text-sm font-medium text-gray-500 mb-4">Homework Summary</p>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3">
                            <span class="text-sm text-emerald-700 font-medium">Correct Answer</span>
                            <span class="text-lg font-bold text-emerald-700"><?php echo (int)$studentStats['correct_answer']; ?></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-rose-200 bg-rose-50 px-4 py-3">
                            <span class="text-sm text-rose-700 font-medium">Incorrect Answer</span>
                            <span class="text-lg font-bold text-rose-700"><?php echo (int)$studentStats['incorrect_answer']; ?></span>
                        </div>
                        <div class="flex items-center justify-between rounded-lg border border-gray-300 bg-gray-100 px-4 py-3">
                            <span class="text-sm text-gray-700 font-medium">Not Submit</span>
                            <span class="text-lg font-bold text-gray-700"><?php echo (int)$studentStats['not_submit']; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 rounded-xl border border-gray-200 p-6 bg-white">
                <p class="text-sm font-medium text-gray-500 mb-4">Upcoming Homework</p>

                <?php if ($upcomingHomework) { ?>
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 rounded-lg border border-blue-200 bg-blue-50 px-4 py-4">
                        <div>
                            <p class="text-base font-semibold text-blue-900"><?php echo htmlspecialchars($upcomingHomework['title']); ?></p>
                            <p class="text-sm text-blue-700 mt-1">Due: <?php echo htmlspecialchars($upcomingHomework['due_date']); ?></p>
                        </div>
                        <a href="view-work.php?id=<?php echo (int)$upcomingHomework['id']; ?>" class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-white text-sm font-medium hover:bg-blue-700 transition">
                            Buka Kerja Rumah
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-4">
                        <p class="text-sm font-medium text-emerald-700">Yeay tiada kerja rumah perlu disiapkan.</p>
                    </div>
                <?php } ?>
            </div>
            <?php } ?>
        </section>
    </main>
</body>
</html>
