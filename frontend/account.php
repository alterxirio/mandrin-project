<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit;
}

$userId = (int)$_SESSION['id'];
$successMessage = '';
$errorMessage = '';

$userStmt = mysqli_prepare($con, 'SELECT id, nama, angkagiliran, role FROM users WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($userStmt, 'i', $userId);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$user = mysqli_fetch_assoc($userResult);

if (!$user) {
    header('Location: ../index.php');
    exit;
}

$isTeacher = $user['role'] === 'Pensyarah';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['save_profile'])) {
        $fullName = trim((string)($_POST['full_name'] ?? ''));
        if ($fullName === '') {
            $errorMessage = 'Nama penuh tidak boleh kosong.';
        } else {
            $stmt = mysqli_prepare($con, 'UPDATE users SET nama = ? WHERE id = ?');
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, 'si', $fullName, $userId);
                mysqli_stmt_execute($stmt);
                $_SESSION['name'] = $fullName;
                $user['nama'] = $fullName;
                $successMessage = 'Maklumat peribadi berjaya dikemas kini.';
            } else {
                $errorMessage = 'Tidak dapat menyimpan maklumat peribadi.';
            }
        }
    }

    if (isset($_POST['change_password'])) {
        $currentPassword = (string)($_POST['current_password'] ?? '');
        $newPassword = (string)($_POST['new_password'] ?? '');
        $confirmPassword = (string)($_POST['confirm_password'] ?? '');

        $userCheckStmt = mysqli_prepare($con, 'SELECT password FROM users WHERE id = ? LIMIT 1');
        if ($userCheckStmt) {
            mysqli_stmt_bind_param($userCheckStmt, 'i', $userId);
            mysqli_stmt_execute($userCheckStmt);
            $result = mysqli_stmt_get_result($userCheckStmt);
            $userRow = mysqli_fetch_assoc($result);

            if (!$userRow || $userRow['password'] !== $currentPassword) {
                $errorMessage = 'Kata laluan semasa tidak tepat.';
            } elseif ($newPassword === '' || strlen($newPassword) < 4) {
                $errorMessage = 'Kata laluan baharu mesti sekurang-kurangnya 4 aksara.';
            } elseif ($newPassword !== $confirmPassword) {
                $errorMessage = 'Pengesahan kata laluan tidak sepadan.';
            } else {
                $updatePassStmt = mysqli_prepare($con, 'UPDATE users SET password = ? WHERE id = ?');
                if ($updatePassStmt) {
                    mysqli_stmt_bind_param($updatePassStmt, 'si', $newPassword, $userId);
                    mysqli_stmt_execute($updatePassStmt);
                    $successMessage = 'Kata laluan berjaya ditukar.';
                } else {
                    $errorMessage = 'Tidak dapat menukar kata laluan.';
                }
            }
        }
    }

    if ($isTeacher && isset($_POST['delete_class'])) {
        $className = trim((string)($_POST['class_name'] ?? ''));
        if ($className !== '') {
            $deleteClassStmt = mysqli_prepare($con, 'DELETE FROM class_students WHERE class = ?');
            if ($deleteClassStmt) {
                mysqli_stmt_bind_param($deleteClassStmt, 's', $className);
                mysqli_stmt_execute($deleteClassStmt);
                $successMessage = 'Kelas ' . htmlspecialchars($className) . ' telah dipadam.';
            }
        }
    }

    if ($isTeacher && isset($_POST['delete_student'])) {
        $className = trim((string)($_POST['class_name'] ?? ''));
        $studentIdToDelete = (int)($_POST['student_id'] ?? 0);

        if ($className !== '' && $studentIdToDelete > 0) {
            $deleteStudentStmt = mysqli_prepare($con, 'DELETE FROM class_students WHERE class = ? AND student_id = ? LIMIT 1');
            if ($deleteStudentStmt) {
                mysqli_stmt_bind_param($deleteStudentStmt, 'si', $className, $studentIdToDelete);
                mysqli_stmt_execute($deleteStudentStmt);
                $successMessage = 'Pelajar berjaya dibuang daripada kelas ' . htmlspecialchars($className) . '.';
            }
        }
    }
}

$scorePercentage = 0;
$totalFinished = 0;
$totalLate = 0;

$studentMetricSql = "
    SELECT
        COUNT(*) AS total_finished,
        SUM(CASE
            WHEN shs.submitted_at IS NOT NULL
                 AND h.due_date IS NOT NULL
                 AND DATE(shs.submitted_at) > h.due_date
            THEN 1 ELSE 0 END) AS total_late,
        AVG(CASE
            WHEN q.total_questions > 0
            THEN (shs.score / q.total_questions) * 100
            ELSE NULL
        END) AS score_percentage
    FROM student_homework_submissions shs
    INNER JOIN homework h ON h.id = shs.homework_id
    LEFT JOIN (
        SELECT homework_id, COUNT(*) AS total_questions
        FROM questions
        GROUP BY homework_id
    ) q ON q.homework_id = shs.homework_id
    WHERE shs.student_id = ?
      AND shs.status = 'submitted'
";

$metricStmt = mysqli_prepare($con, $studentMetricSql);
mysqli_stmt_bind_param($metricStmt, 'i', $userId);
mysqli_stmt_execute($metricStmt);
$metricResult = mysqli_stmt_get_result($metricStmt);
$metrics = mysqli_fetch_assoc($metricResult) ?: [];

$totalFinished = (int)($metrics['total_finished'] ?? 0);
$totalLate = (int)($metrics['total_late'] ?? 0);
$scorePercentage = (float)($metrics['score_percentage'] ?? 0);
$scorePercentageDisplay = round($scorePercentage);

$teacherClassRows = [];
if ($isTeacher) {
    $classResult = mysqli_query(
        $con,
        "SELECT class, COUNT(*) AS total_students
         FROM class_students
         GROUP BY class
         ORDER BY class ASC"
    );

    while ($classResult && ($classRow = mysqli_fetch_assoc($classResult))) {
        $className = (string)$classRow['class'];

        $studentStmt = mysqli_prepare(
            $con,
            "SELECT
                u.id,
                u.nama,
                u.angkagiliran,
                COALESCE(ROUND(AVG(CASE WHEN q.total_questions > 0 THEN (shs.score / q.total_questions) * 100 END)), 0) AS average_score,
                COALESCE(SUM(CASE
                    WHEN shs.submitted_at IS NOT NULL
                         AND h.due_date IS NOT NULL
                         AND DATE(shs.submitted_at) > h.due_date
                    THEN 1 ELSE 0
                END), 0) AS total_late,
                COALESCE(SUM(CASE WHEN h.id IS NOT NULL THEN 1 ELSE 0 END), 0) AS total_submitted
             FROM class_students cs
             INNER JOIN users u
                ON u.id = cs.student_id
                AND u.role = 'Pelajar'
             LEFT JOIN student_homework_submissions shs
                ON shs.student_id = u.id
                AND shs.status = 'submitted'
             LEFT JOIN homework h
                ON h.id = shs.homework_id
                AND h.class = cs.class
             LEFT JOIN (
                SELECT homework_id, COUNT(*) AS total_questions
                FROM questions
                GROUP BY homework_id
             ) q ON q.homework_id = shs.homework_id
             WHERE cs.class = ?
             GROUP BY u.id, u.nama, u.angkagiliran
             ORDER BY u.nama ASC"
        );

        $students = [];
        if ($studentStmt) {
            mysqli_stmt_bind_param($studentStmt, 's', $className);
            mysqli_stmt_execute($studentStmt);
            $studentResult = mysqli_stmt_get_result($studentStmt);
            while ($studentResult && ($studentRow = mysqli_fetch_assoc($studentResult))) {
                $students[] = $studentRow;
            }
        }

        $teacherClassRows[] = [
            'class' => $className,
            'total_students' => (int)$classRow['total_students'],
            'students' => $students,
        ];
    }
}

$lastLoginDisplay = date('j F Y');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <?php include('header.php'); ?>
    <link rel="stylesheet" href="../css/account.css">
</head>
<body class="bg-gray-100">
    <?php include('navbar.php'); ?>

    <main class="account-page-wrap">
        <section class="account-shell">
            <h1 class="account-title">Account</h1>

            <?php if ($successMessage !== '') { ?>
                <div class="alert-success"><?php echo $successMessage; ?></div>
            <?php } ?>

            <?php if ($errorMessage !== '') { ?>
                <div class="alert-error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php } ?>

            <div class="profile-card">
                <div>
                    <h2 class="profile-name"><?php echo htmlspecialchars((string)($user['nama'] ?? '-')); ?></h2>
                    <p class="profile-meta">Student ID: <?php echo htmlspecialchars((string)($user['angkagiliran'] ?? '-')); ?></p>
                    <p class="profile-meta">Last login: <?php echo htmlspecialchars($lastLoginDisplay); ?></p>
                </div>
                <button type="button" class="edit-profile-btn">Edit Profile</button>
            </div>

            <div class="account-grid">
                <section class="account-card">
                    <h3>User Info</h3>
                    <form method="post" class="card-form">
                        <label>Full Name
                            <input type="text" name="full_name" value="<?php echo htmlspecialchars((string)($user['nama'] ?? '')); ?>" required>
                        </label>
                        <label>Student ID
                            <input type="text" value="<?php echo htmlspecialchars((string)($user['angkagiliran'] ?? '')); ?>" readonly>
                        </label>
                        <label>Role
                            <input type="text" value="<?php echo htmlspecialchars((string)($user['role'] ?? '')); ?>" readonly>
                        </label>
                        <button type="submit" name="save_profile" class="primary-btn">Save Changes</button>
                    </form>
                </section>

                <section class="account-card">
                    <h3>Security</h3>
                    <form method="post" class="card-form">
                        <label>Current Password
                            <input type="password" name="current_password" required>
                        </label>
                        <label>New Password
                            <input type="password" name="new_password" required>
                        </label>
                        <label>Confirm Password
                            <input type="password" name="confirm_password" required>
                        </label>
                        <button type="submit" name="change_password" class="danger-btn">Change Password</button>
                    </form>
                </section>
            </div>

            <?php if (!$isTeacher) { ?>
                <section class="account-card dashboard-card">
                    <h3>Dashboard</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Score Percentage</span>
                            <strong><?php echo $scorePercentageDisplay; ?>%</strong>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Finish Homework</span>
                            <strong><?php echo $totalFinished; ?></strong>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Total Late</span>
                            <strong><?php echo $totalLate; ?></strong>
                        </div>
                    </div>
                </section>
            <?php } else { ?>
                <section class="account-card dashboard-card">
                    <h3>Teacher Dashboard - Class List</h3>

                    <?php if (empty($teacherClassRows)) { ?>
                        <p class="empty-text">No class found.</p>
                    <?php } ?>

                    <?php foreach ($teacherClassRows as $classItem) { ?>
                        <details class="class-card">
                            <summary>
                                <div>
                                    <strong>Class <?php echo htmlspecialchars($classItem['class']); ?></strong>
                                </div>
                                <div class="class-actions">
                                    <span class="student-count"><?php echo (int)$classItem['total_students']; ?> students</span>
                                    <form method="post" onsubmit="return confirm('Delete this class list?');">
                                        <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($classItem['class']); ?>">
                                        <button type="submit" name="delete_class" class="danger-outline-btn">Delete Class</button>
                                    </form>
                                    <span class="dropdown-icon">⌄</span>
                                </div>
                            </summary>

                            <div class="student-table-wrap">
                                <table class="student-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>ID</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($classItem['students'])) { ?>
                                            <tr><td colspan="3">No student in this class.</td></tr>
                                        <?php } ?>

                                        <?php foreach ($classItem['students'] as $student) { ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars((string)$student['nama']); ?></td>
                                                <td><?php echo htmlspecialchars((string)$student['angkagiliran']); ?></td>
                                                <td>
                                                    <details class="mini-view">
                                                        <summary class="view-btn">View</summary>
                                                        <div class="student-metrics">
                                                            <div><span>Average Score</span><strong><?php echo (int)$student['average_score']; ?>%</strong></div>
                                                            <div><span>Late Submission</span><strong><?php echo (int)$student['total_late']; ?></strong></div>
                                                            <div><span>Total Submitted</span><strong><?php echo (int)$student['total_submitted']; ?></strong></div>
                                                        </div>
                                                    </details>

                                                    <form method="post" class="inline-form" onsubmit="return confirm('Delete this student from class?');">
                                                        <input type="hidden" name="class_name" value="<?php echo htmlspecialchars($classItem['class']); ?>">
                                                        <input type="hidden" name="student_id" value="<?php echo (int)$student['id']; ?>">
                                                        <button type="submit" name="delete_student" class="delete-student-btn">Delete Student</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </details>
                    <?php } ?>
                </section>
            <?php } ?>
        </section>
    </main>
</body>
</html>
