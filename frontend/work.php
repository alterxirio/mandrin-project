<?php
session_start();
include('../config/config.php');

$isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah';

$sql = 'SELECT * FROM homework ORDER BY id DESC';
$result = mysqli_query($con, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homework</title>
    <?php include('header.php'); ?>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('navbar.php'); ?>

<div class="grid place-items-center bg-gray-100">
  <div class="w-[80vw] bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden mb-10">
    <div class="px-8 py-5 border-b border-gray-300 bg-white">
      <h2 class="text-xl font-semibold text-gray-900">📚 Homework</h2>
      <p class="text-sm text-gray-500 mt-1"><?php echo $isTeacher ? 'Urus dan semak tugasan pelajar' : 'Your assigned tasks'; ?></p>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-50 border-gray-300 border-b">
          <tr>
            <th class="px-8 py-3 font-medium">Title</th>
            <th class="px-8 py-3 font-medium text-right">Action</th>
          </tr>
        </thead>
        <tbody class="divide-y">
          <?php
            $sql = 'SELECT * FROM homework ORDER BY id DESC';
            $result = mysqli_query($con, $sql);
          ?>
          <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <tr class="hover:bg-gray-50 border-gray-300 transition">
              <td class="px-8 py-5 font-medium">
                <?php echo htmlspecialchars($row['title']); ?>
                <p class="text-xs text-gray-500 mt-1">Due: <?php echo ($row['due_date']); ?></p>
              </td>
              <td class="px-8 py-5 text-right">
                <?php if ($isTeacher): ?>
                  <div class="flex items-center justify-end gap-2">
                    <a href="view-work.php?id=<?php echo (int)$row['id']; ?>" class="px-3 py-1.5 text-sm rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">View</a>
                    <a href="edit-work.php?id=<?php echo (int)$row['id']; ?>" class="px-3 py-1.5 text-sm rounded-full bg-amber-500 text-white hover:bg-amber-600 transition">Edit</a>
                    <button
                      type="button"
                      class="delete-homework px-3 py-1.5 text-sm rounded-full bg-red-600 text-white hover:bg-red-700 transition"
                      data-id="<?php echo (int)$row['id']; ?>"
                    >Delete</button>
                  </div>
                <?php else: ?>
                  <?php
                    $studentId = isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0;
                    $checkSubmission = mysqli_query($con, "SELECT status FROM student_homework_submissions WHERE homework_id = {$row['id']} AND student_id = {$studentId} LIMIT 1");
                    $submissionStatus = $checkSubmission ? mysqli_fetch_assoc($checkSubmission) : null;
                  ?>

                  <?php if ($submissionStatus && $submissionStatus['status'] === 'submitted'): ?>
                    <button class="px-4 py-1.5 text-sm rounded-full bg-green-600 text-white cursor-default">Submitted</button>
                  <?php else: ?>
                    <a href="view-work.php?id=<?php echo (int)$row['id']; ?>" class="px-4 py-1.5 text-sm rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">View</a>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>

  <?php if ($isTeacher): ?>
    <a href="add-work.php"><button class="w-[80vw] mt-6 flex items-center justify-center border-2 border-dashed border-gray-400 bg-gray-100 text-gray-700 text-3xl" style="border-radius: 22px; height: 150px;">+</button></a>
  <?php endif; ?>
</div>

<script>
document.querySelectorAll('.delete-homework').forEach((button) => {
  button.addEventListener('click', async () => {
    const homeworkId = button.dataset.id;
    if (!confirm('Padam kerja rumah ini? Tindakan ini tidak boleh dibatalkan.')) return;

    button.disabled = true;
    try {
      const formData = new FormData();
      formData.append('action', 'delete');
      formData.append('homework_id', homeworkId);

      const response = await fetch('../backend/homework-manageBE.php', { method: 'POST', body: formData });
      const result = await response.json();
      if (!response.ok || !result.success) throw new Error(result.message || 'Gagal memadam.');

      window.location.reload();
    } catch (error) {
      alert(error.message);
      button.disabled = false;
    }
  });
});
</script>
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</body>
</html>
