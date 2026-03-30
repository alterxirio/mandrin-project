<?php
session_start();
include('../config/config.php');

if (isset($_POST['logout'])) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

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
$teacherClassStats = [];
$isTeacher = isset($_SESSION['role']) && strtolower((string)$_SESSION['role']) === 'pensyarah';
$availableClasses = [];

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

if ($isTeacher && $con instanceof mysqli) {
    $availableClassSql = "
        SELECT class_name FROM (
            SELECT DISTINCT class AS class_name FROM class_students WHERE class IS NOT NULL AND class <> ''
            UNION
            SELECT DISTINCT class AS class_name FROM homework WHERE class IS NOT NULL AND class <> ''
        ) AS class_pool
        ORDER BY class_name ASC
    ";

    $availableClassResult = mysqli_query($con, $availableClassSql);
    if ($availableClassResult) {
        while ($availableClassRow = mysqli_fetch_assoc($availableClassResult)) {
            $className = trim((string)($availableClassRow['class_name'] ?? ''));
            if ($className !== '') {
                $availableClasses[] = $className;
            }
        }
    }

    $teacherClassSql = "
        SELECT
            c.class,
            COUNT(DISTINCT c.student_id) AS total_students,
            COUNT(DISTINCT h.id) AS total_homework,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.score ELSE 0 END), 0) AS total_correct,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN shs.incorrect ELSE 0 END), 0) AS total_incorrect,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN q.total_questions ELSE 0 END), 0) AS total_questions_submitted,
            COALESCE(SUM(CASE WHEN LOWER(COALESCE(shs.status, '')) = 'submitted' THEN 1 ELSE 0 END), 0) AS total_submitted
        FROM class_students c
        LEFT JOIN homework h ON h.class = c.class
        LEFT JOIN (
            SELECT homework_id, COUNT(*) AS total_questions
            FROM questions
            GROUP BY homework_id
        ) q ON q.homework_id = h.id
        LEFT JOIN student_homework_submissions shs
            ON shs.homework_id = h.id
            AND shs.student_id = c.student_id
        GROUP BY c.class
        ORDER BY c.class ASC
    ";

    $teacherClassResult = mysqli_query($con, $teacherClassSql);
    if ($teacherClassResult) {
        while ($teacherClassRow = mysqli_fetch_assoc($teacherClassResult)) {
            $totalQuestionsSubmitted = (int)($teacherClassRow['total_questions_submitted'] ?? 0);
            $totalCorrect = (int)($teacherClassRow['total_correct'] ?? 0);
            $averageScore = 0;

            if ($totalQuestionsSubmitted > 0) {
                $averageScore = round(($totalCorrect / $totalQuestionsSubmitted) * 100, 1);
            }

            $totalHomework = (int)($teacherClassRow['total_homework'] ?? 0);
            $totalStudents = (int)($teacherClassRow['total_students'] ?? 0);

            $teacherClassStats[] = [
                'class' => (string)($teacherClassRow['class'] ?? '-'),
                'total_students' => $totalStudents,
                'average_score' => $averageScore,
                'correct_answer' => $totalCorrect,
                'incorrect_answer' => (int)($teacherClassRow['total_incorrect'] ?? 0),
                'not_submit' => max(0, ($totalHomework * $totalStudents) - (int)($teacherClassRow['total_submitted'] ?? 0)),
            ];
        }
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
        <?php if ($isTeacher && isset($_GET['create_student']) && $_GET['create_student'] === 'success') { ?>
            <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                Pelajar berjaya ditambah.
            </div>
        <?php } ?>

        <?php if ($isTeacher && isset($_GET['create_student']) && $_GET['create_student'] === 'error') { ?>
            <div class="mb-4 rounded-lg border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Gagal menambah pelajar. Sila semak maklumat dan cuba lagi.
            </div>
        <?php } ?>

        <?php if ($isTeacher && isset($_GET['create_student']) && $_GET['create_student'] === 'duplicate') { ?>
            <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Nama pelajar atau angka giliran sudah digunakan.
            </div>
        <?php } ?>

        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Akaun Saya</h1>
                    <p class="text-gray-600 mt-1">Maklumat ringkas akaun anda.</p>
                </div>
                <?php if (!$isStudent) { ?>
                    <a href="../frontend/edit-profile.php" class="inline-flex items-center justify-center rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">
                        Sunting Profil
                    </a>
                <?php } ?>
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

            <?php if ($isTeacher) { ?>
                <div class="mt-8 space-y-4">
                    <h2 class="text-xl font-bold text-gray-900">Status Kelas</h2>
                    <?php if (count($teacherClassStats) > 0) { ?>
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                            <?php foreach ($teacherClassStats as $classStat) { ?>
                                <article class="rounded-xl border border-gray-200 bg-white p-5">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="text-sm text-gray-500">Kelas</p>
                                            <h3 class="text-lg font-bold text-gray-900"><?php echo htmlspecialchars($classStat['class']); ?></h3>
                                            <p class="text-sm text-gray-600 mt-1"><?php echo (int)$classStat['total_students']; ?> pelajar</p>
                                        </div>
                                        <div class="flex flex-col items-end gap-2">
                                            <button
                                                type="button"
                                                data-modal-target="add-student-modal"
                                                data-modal-toggle="add-student-modal"
                                                data-selected-class="<?php echo htmlspecialchars($classStat['class']); ?>"
                                                class="inline-flex items-center justify-center rounded-lg bg-[#B71C1C] px-4 py-2 text-white text-sm font-medium hover:bg-[#8E1616] transition add-student-btn"
                                            >
                                                Tambah Pelajar
                                            </button>
                                            <span class="text-2xl font-bold text-red-600"><?php echo number_format((float)$classStat['average_score'], 1); ?>%</span>
                                        </div>
                                    </div>
                                    <div class="mt-4 grid grid-cols-3 gap-2 text-center">
                                        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-2">
                                            <p class="text-xs text-emerald-700">Betul</p>
                                            <p class="text-lg font-semibold text-emerald-700"><?php echo (int)$classStat['correct_answer']; ?></p>
                                        </div>
                                        <div class="rounded-lg border border-rose-200 bg-rose-50 p-2">
                                            <p class="text-xs text-rose-700">Salah</p>
                                            <p class="text-lg font-semibold text-rose-700"><?php echo (int)$classStat['incorrect_answer']; ?></p>
                                        </div>
                                        <div class="rounded-lg border border-gray-300 bg-gray-100 p-2">
                                            <p class="text-xs text-gray-700">Belum Hantar</p>
                                            <p class="text-lg font-semibold text-gray-700"><?php echo (int)$classStat['not_submit']; ?></p>
                                        </div>
                                    </div>
                                    <a href="class-students.php?class=<?php echo urlencode($classStat['class']); ?>" class="mt-4 inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition">
                                        Lihat Pelajar
                                    </a>
                                </article>
                            <?php } ?>
                        </div>
                    <?php } else { ?>
                        <div class="rounded-lg border border-gray-300 bg-gray-50 p-4">
                            <p class="text-sm text-gray-600">Belum ada kelas untuk dipaparkan.</p>
                        </div>
                    <?php } ?>
                    
                </div>
            <?php } ?>

        </section>

        <form method="post">
            <button class="inline-flex items-center mt-4 justify-center rounded-lg bg-red-600 px-6 py-4 text-sm font-medium text-white hover:bg-red-700 transition"  type="submit" name="logout">Log Keluar</button>
        </form>

    </main>

    <?php if ($isTeacher) { ?>
        <div id="add-student-modal" data-modal-backdrop="static" tabindex="-1" aria-hidden="true" class="hidden fixed inset-0 z-50 flex justify-center items-center bg-black/80 backdrop-blur-sm">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow-sm">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t border-gray-200">
                        <h3 class="text-xl font-semibold text-gray-900">Tambah Pelajar</h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-hide="add-student-modal">
                            <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 12 12M13 1 1 13"/>
                            </svg>
                            <span class="sr-only">Close modal</span>
                        </button>
                    </div>

                    <form action="../backend/student-createBE.php" method="post" class="p-4 md:p-5 space-y-4">
                        <div>
                            <label for="student_name" class="block mb-2 text-sm font-medium text-gray-900">Nama Pelajar (Username)</label>
                            <input type="text" id="student_name" name="student_name" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-200 focus:border-red-400 block w-full p-2.5" placeholder="Contoh: AHMAD BIN ALI">
                        </div>

                        <div>
                            <label for="student_password" class="block mb-2 text-sm font-medium text-gray-900">Kata Laluan (Angka Giliran)</label>
                            <input type="text" id="student_password" name="student_password" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-200 focus:border-red-400 block w-full p-2.5" placeholder="Masukkan angka giliran pelajar">
                            <p class="mt-1 text-xs text-gray-500">Sila masukkan angka giliran pelajar.</p>
                        </div>

                        <div>
                            <label for="student_class" class="block mb-2 text-sm font-medium text-gray-900">Kelas</label>
                            <select id="student_class" name="student_class" required class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-red-200 focus:border-red-400 block w-full p-2.5">
                                <option value="">-- Pilih kelas --</option>
                                <?php foreach ($availableClasses as $availableClass) { ?>
                                    <option value="<?php echo htmlspecialchars($availableClass); ?>"><?php echo htmlspecialchars($availableClass); ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-2">
                            <button type="button" data-modal-hide="add-student-modal" class="py-2 px-4 text-sm font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-100">Batal</button>
                            <button type="submit" class="text-white bg-[#B71C1C] hover:bg-[#8E1616] focus:ring-4 focus:outline-none focus:ring-red-200 font-medium rounded-lg text-sm px-5 py-2.5 text-center">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const studentNameInput = document.getElementById('student_name');
                const classSelect = document.getElementById('student_class');

                if (studentNameInput) {
                    studentNameInput.addEventListener('input', function () {
                        this.value = this.value.toUpperCase();
                    });
                }

                document.querySelectorAll('.add-student-btn').forEach(function (button) {
                    button.addEventListener('click', function () {
                        const selectedClass = this.getAttribute('data-selected-class') || '';
                        if (classSelect && selectedClass !== '') {
                            classSelect.value = selectedClass;
                        }
                    });
                });
            });
        </script>
    <?php } ?>
</body>
</html>
