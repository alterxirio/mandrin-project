<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || strtolower((string)$_SESSION['role']) !== 'pensyarah') {
    header('Location: account.php');
    exit;
}

$allowedPrograms = ['KPD', 'BAK', 'BPM', 'KMK', 'HBP', 'HSK'];
$classOptions = [];
for ($year = 2024; $year <= 2035; $year++) {
    foreach ($allowedPrograms as $program) {
        $classOptions[] = '2 DVM ' . $program . ' ' . $year;
    }
}

$studentId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$student = null;

if ($studentId > 0 && $con instanceof mysqli) {
    $studentSql = "
        SELECT u.id, u.nama, cs.class
        FROM users u
        LEFT JOIN class_students cs ON cs.student_id = u.id
        WHERE u.id = ?
        LIMIT 1
    ";

    $studentStmt = mysqli_prepare($con, $studentSql);
    if ($studentStmt) {
        mysqli_stmt_bind_param($studentStmt, 'i', $studentId);
        mysqli_stmt_execute($studentStmt);
        $studentResult = mysqli_stmt_get_result($studentStmt);
        $student = $studentResult ? mysqli_fetch_assoc($studentResult) : null;
    }
}

if (!$student) {
    header('Location: account.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Pelajar</title>
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-50">
    <?php include('navbar.php'); ?>

    <main class="max-w-3xl mx-auto px-4 py-6">
        <section class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h1 class="text-2xl font-bold text-gray-900">Edit Pelajar</h1>
            <p class="text-gray-600 mt-1">Guru hanya boleh edit nama dan kelas pelajar.</p>

            <form action="../backend/student-updateBE.php" method="post" class="mt-6 grid grid-cols-1 gap-4">
                <input type="hidden" name="student_id" value="<?php echo (int)$student['id']; ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Pelajar</label>
                    <input type="text" name="student_name" required value="<?php echo htmlspecialchars((string)($student['nama'] ?? '')); ?>" class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kelas</label>
                    <select name="student_class" required class="w-full rounded-lg border border-gray-300 px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-200 focus:border-red-400">
                        <option value="">-- Pilih kelas --</option>
                        <?php foreach ($classOptions as $classOption) { ?>
                            <option value="<?php echo htmlspecialchars($classOption); ?>" <?php echo ((string)($student['class'] ?? '') === $classOption) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($classOption); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" class="rounded-lg bg-[#B71C1C] px-4 py-2 text-white font-medium hover:bg-[#8E1616] transition">Simpan</button>
                    <a href="javascript:history.back()" class="rounded-lg bg-gray-600 px-4 py-2 text-white font-medium hover:bg-gray-700 transition">Kembali</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
