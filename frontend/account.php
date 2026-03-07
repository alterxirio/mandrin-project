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
}

$userStmt = mysqli_prepare($con, 'SELECT id, nama, angkagiliran, role FROM users WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($userStmt, 'i', $userId);
mysqli_stmt_execute($userStmt);
$userResult = mysqli_stmt_get_result($userStmt);
$user = mysqli_fetch_assoc($userResult);

$scorePercentage = 0;
$totalFinished = 0;
$totalLate = 0;

if ($user && $user['role'] === 'Pelajar') {
    $metricsSql = "
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

    $metricsStmt = mysqli_prepare($con, $metricsSql);
    mysqli_stmt_bind_param($metricsStmt, 'i', $userId);
    mysqli_stmt_execute($metricsStmt);
    $metricsResult = mysqli_stmt_get_result($metricsStmt);
    $metrics = mysqli_fetch_assoc($metricsResult) ?: [];

    $totalFinished = (int)($metrics['total_finished'] ?? 0);
    $totalLate = (int)($metrics['total_late'] ?? 0);
    $scorePercentage = (float)($metrics['score_percentage'] ?? 0);
} else {
    $metricsSql = "
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
        WHERE shs.status = 'submitted'
    ";

    $metricsResult = mysqli_query($con, $metricsSql);
    $metrics = mysqli_fetch_assoc($metricsResult) ?: [];

    $totalFinished = (int)($metrics['total_finished'] ?? 0);
    $totalLate = (int)($metrics['total_late'] ?? 0);
    $scorePercentage = (float)($metrics['score_percentage'] ?? 0);
}

$scorePercentageDisplay = round($scorePercentage);
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
                <div class="alert-success"><?php echo htmlspecialchars($successMessage); ?></div>
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
                <button type="button" class="edit-profile-btn">
                    Edit Profile
                </button>
            </div>

            <div class="account-grid">
                <section class="account-card outlined-black">
                    <h3>Personal Information</h3>
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

                <section class="account-card outlined-black">
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

                <section class="account-card outlined-orange stats-card">
                    <h3><?php echo ($user['role'] ?? '') === 'Pelajar' ? 'Student Dashboard' : 'Submission Dashboard'; ?></h3>
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
            </div>
        </section>
    </main>
</body>
</html>
