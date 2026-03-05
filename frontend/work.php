<?php session_start(); ?>
<?php include('../config/config.php'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Page</title>

    <?php include("header.php"); ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
<?php include("navbar.php"); ?>

<div class=" grid place-items-center bg-gray-100">

  <div class="w-[80vw] bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden">
    
    <div class="px-8 py-5 border-b border-gray-300 bg-white">
      <h2 class="text-xl font-semibold text-gray-900">📚 Homework</h2>
      <p class="text-sm text-gray-500 mt-1"><?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah') ? 'Manage and edit homework' : 'Your assigned tasks'; ?></p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-50 border-gray-300 border-b">
          <tr>
            <th class="px-8 py-3 font-medium">Title</th>
            <th class="px-8 py-3 font-medium text-right">Action</th>
          </tr>
        </thead>

        <?php
        $isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah';

        if ($isTeacher) {
          $homeworkSql = "
            SELECT
              h.*,
              (SELECT COUNT(*) FROM questions q WHERE q.homework_id = h.id) AS total_questions
            FROM homework h
            ORDER BY h.due_date ASC, h.id DESC
          ";
          $result = mysqli_query($con, $homeworkSql);
        } else {
          $studentId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
          $result = false;

          $homeworkStmt = mysqli_prepare(
            $con,
            "SELECT
                h.*,
                (SELECT COUNT(*) FROM questions q WHERE q.homework_id = h.id) AS total_questions
             FROM homework h
             INNER JOIN class_students cs ON cs.class = h.class
             WHERE cs.student_id = ?
             ORDER BY h.due_date ASC, h.id DESC"
          );

          if ($homeworkStmt) {
            mysqli_stmt_bind_param($homeworkStmt, 'i', $studentId);
            mysqli_stmt_execute($homeworkStmt);
            $result = mysqli_stmt_get_result($homeworkStmt);
          }
        }
        ?>
        
        <tbody class="divide-y">

          <?php while($result && ($row = mysqli_fetch_assoc($result))) {
            $homeworkId = (int)$row['id'];
            $totalQuestions = max(1, (int)$row['total_questions']);

            $submissionCount = 0;
            $classSize = 0;
            $studentsByHomework = [];

            if ($isTeacher) {
              $classKey = mysqli_real_escape_string($con, $row['class']);
              $studentSql = "
                SELECT
                  u.id AS student_id,
                  u.nama AS student_name,
                  cs.class,
                  shs.id AS submission_id,
                  shs.status,
                  shs.submitted_at,
                  shs.score
                FROM class_students cs
                INNER JOIN users u
                  ON u.id = cs.student_id
                  AND u.role = 'Pelajar'
                LEFT JOIN student_homework_submissions shs
                  ON shs.homework_id = {$homeworkId}
                  AND shs.student_id = u.id
                WHERE cs.class = '{$classKey}'
                ORDER BY u.nama ASC
              ";

              $studentResult = mysqli_query($con, $studentSql);

              while ($studentRow = mysqli_fetch_assoc($studentResult)) {
                $statusValue = strtolower((string)($studentRow['status'] ?? ''));
                $isSubmitted = $statusValue === 'submitted';
                $dueTimestamp = strtotime((string)$row['due_date']);
                $submittedAtTimestamp = strtotime((string)($studentRow['submitted_at'] ?? ''));
                $isLate = $isSubmitted && $dueTimestamp !== false && $submittedAtTimestamp !== false && $submittedAtTimestamp > $dueTimestamp;
                if ($isSubmitted) {
                  $submissionCount++;
                }

                $studentStatusText = 'Not submitted';
                $studentStatusBadge = 'bg-rose-100 text-rose-700';
                if ($isLate) {
                  $studentStatusText = 'Late';
                  $studentStatusBadge = 'bg-amber-100 text-amber-700';
                } elseif ($isSubmitted) {
                  $studentStatusText = 'Submitted';
                  $studentStatusBadge = 'bg-emerald-100 text-emerald-700';
                }

                $studentsByHomework[] = [
                  'student_id' => (int)$studentRow['student_id'],
                  'student_name' => $studentRow['student_name'],
                  'status' => $studentStatusText,
                  'status_badge' => $studentStatusBadge,
                  'score' => $isSubmitted ? ((int)$studentRow['score']) : 0,
                  'is_submitted' => $isSubmitted
                ];
              }

              $classSize = count($studentsByHomework);
            }
          ?>

            <tr class="hover:bg-gray-50 border-gray-300 transition">
              <td class="px-8 py-5 font-medium align-top">
                <?php echo htmlspecialchars($row['title']); ?>
                <p class="text-xs text-gray-500 mt-1">Due: <?php echo htmlspecialchars($row['due_date']); ?></p>
                <?php if ($isTeacher) { ?>
                  <p class="text-xs text-gray-500 mt-1">Class: <?php echo htmlspecialchars($row['class']); ?></p>
                <?php } ?>
              </td>
              <td class="px-8 py-5 text-right align-top">
                <?php if ($isTeacher) { ?>
                  <div class="inline-flex items-center gap-2">
                    <span class="text-xs font-semibold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">
                      <?php echo $submissionCount . '/' . $classSize; ?> submitted
                    </span>

                    <button
                      type="button"
                      class="homework-expand-toggle h-8 w-8 rounded-full border-2 border-gray-800 text-gray-800 flex items-center justify-center hover:bg-gray-100 transition"
                      data-homework-toggle-id="<?php echo $homeworkId; ?>"
                      aria-expanded="false"
                      aria-controls="homework-expand-<?php echo $homeworkId; ?>"
                    >
                      <span class="material-symbols-outlined text-base">expand_more</span>
                    </button>

                    <a href="edit-work.php?id=<?php echo $homeworkId; ?>">
                      <button class="px-4 py-1.5 text-sm rounded-full bg-amber-600 text-white hover:bg-amber-700 transition">
                        Edit
                      </button>
                    </a>
                    <button type="button" data-homework-delete-id="<?php echo $homeworkId; ?>" class="px-4 py-1.5 text-sm rounded-full bg-red-600 text-white hover:bg-red-700 transition">
                      Delete
                    </button>
                  </div>
                <?php } else {
                  $checkSubmission = mysqli_query($con, "SELECT status FROM student_homework_submissions WHERE homework_id = {$homeworkId} AND student_id = {$_SESSION['id']}");
                  $submissionStatus = mysqli_fetch_assoc($checkSubmission);
                  $statusValue = strtolower((string)($submissionStatus['status'] ?? ''));
                  $alreadySubmitted = $statusValue === 'submitted';
                  $isPastDue = strtotime((string)$row['due_date']) < time();

                  if ($alreadySubmitted) {
                  ?>
                      <button class="px-4 py-1.5 text-sm rounded-full bg-green-600 text-white cursor-default">
                        Submitted
                      </button>
                  <?php } elseif ($isPastDue) { ?>
                      <button class="px-4 py-1.5 text-sm rounded-full bg-red-600 text-white cursor-not-allowed" disabled>
                        Late
                      </button>
                  <?php } else { ?>
                      <a href="view-work.php?id=<?php echo $homeworkId; ?>">
                        <button class="px-4 py-1.5 text-sm rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                          View
                        </button>
                      </a>
                  <?php }
                  } ?>
              </td>
            </tr>

            <?php if ($isTeacher) { ?>
              <tr id="homework-expand-<?php echo $homeworkId; ?>" class="hidden bg-gray-50 border-b border-gray-200">
                <td colspan="2" class="px-8 py-4">
                  <div class="space-y-3">
                    <?php if (empty($studentsByHomework)) { ?>
                      <p class="text-sm text-gray-500">No students found for class <?php echo htmlspecialchars($row['class']); ?>.</p>
                    <?php } else { ?>
                      <?php foreach ($studentsByHomework as $studentInfo) { ?>
                        <div class="bg-white border border-gray-200 rounded-lg p-4 grid grid-cols-1 md:grid-cols-12 gap-3 items-center">
                          <div class="md:col-span-4 font-medium text-emerald-700"><?php echo htmlspecialchars($studentInfo['student_name']); ?></div>
                          <div class="md:col-span-3">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full <?php echo $studentInfo['status_badge']; ?>">
                              <?php echo $studentInfo['status']; ?>
                            </span>
                          </div>
                          <div class="md:col-span-2 text-sm font-medium text-amber-600">Score: <?php echo $studentInfo['score'] . '/' . $totalQuestions; ?></div>
                          <div class="md:col-span-3 md:text-right">
                            <?php if ($studentInfo['is_submitted']) { ?>
                              <a href="view-student-work.php?homework_id=<?php echo $homeworkId; ?>&student_id=<?php echo $studentInfo['student_id']; ?>" class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                                View submitted work
                              </a>
                            <?php } else { ?>
                              <button type="button" disabled class="inline-flex items-center justify-center px-3 py-1.5 text-xs rounded-full bg-gray-300 text-gray-600 cursor-not-allowed">
                                View submitted work
                              </button>
                            <?php } ?>
                          </div>
                        </div>
                      <?php } ?>
                    <?php } ?>
                  </div>
                </td>
              </tr>
            <?php } ?>

          <?php } ?>
        </tbody>
      </table>
    </div>

  </div>

  <?php if ($isTeacher) { ?>
  <a href="../frontend/add-work.php"><button class="w-[80vw] mt-6 flex items-center justify-center border-2 border-dashed border-gray-400 bg-gray-100 text-gray-700 text-3xl" style="border-radius: 22px; height: 150px;">+</button></a>
<?php } ?>
</div>

<script>
document.querySelectorAll('[data-homework-delete-id]').forEach((button) => {
  button.addEventListener('click', async () => {
    const homeworkId = button.getAttribute('data-homework-delete-id');
    const shouldDelete = window.confirm('Adakah anda pasti mahu padam kerja rumah ini?');
    if (!shouldDelete) return;

    try {
      const response = await fetch('../backend/homework-deleteBE.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `homework_id=${encodeURIComponent(homeworkId)}`
      });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Gagal memadam kerja rumah.');

      window.location.reload();
    } catch (error) {
      alert(error.message || 'Gagal memadam kerja rumah.');
    }
  });
});

document.querySelectorAll('[data-homework-toggle-id]').forEach((toggleButton) => {
  toggleButton.addEventListener('click', () => {
    const homeworkId = toggleButton.getAttribute('data-homework-toggle-id');
    const expandedRow = document.getElementById(`homework-expand-${homeworkId}`);
    if (!expandedRow) return;

    const isHidden = expandedRow.classList.contains('hidden');
    expandedRow.classList.toggle('hidden');
    toggleButton.setAttribute('aria-expanded', isHidden ? 'true' : 'false');

    const icon = toggleButton.querySelector('.material-symbols-outlined');
    if (icon) {
      icon.textContent = isHidden ? 'expand_less' : 'expand_more';
    }
  });
});
</script>

</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</html>
