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


<!-- <div class="max-w-7xl h-[25vh] md:h-[75vh] portrait:h-[50vh] mx-auto mt-6 p-4">
  <div class="bg-white rounded-lg shadow-md h-full">

    <div class="border-b p-4">
      <h2 class="text-lg font-semibold portrait:text-4xl">Ongoing Work</h2>
    </div>

    <div class="flex flex-col items-center justify-center h-full text-center">
      <p class="text-2xl portrait:text-4xl">🎉</p>
      <h3 class="text-lg font-semibold mt-2 portrait:text-4xl">
        Yeay! Tiada kerja yang perlu dihantar
      </h3>
      <p class="text-gray-500 text-sm mt-1 portrait:text-4xl">
        Enjoy your free time 😎
      </p>
    </div>

      
      <ul class="divide-y">
        <li class="p-4 flex justify-between items-center hover:bg-gray-50">
          <div>
            <p class="font-medium">Mandarin Homework 1</p>
            <p class="text-sm text-gray-500">Due: 30 Jan 2026</p>
          </div>
          <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-700 rounded-full">
            Pending
          </span>
        </li>
      </ul>
     

    </div>

</div> -->

<div class=" grid place-items-center bg-gray-100">

  <div class="w-[80vw] bg-white shadow-md rounded-xl border border-gray-200 overflow-hidden">
    
    <!-- HEADER -->
    <div class="px-8 py-5 border-b border-gray-300 bg-white">
      <h2 class="text-xl font-semibold text-gray-900">📚 Homework</h2>
      <p class="text-sm text-gray-500 mt-1"><?php echo (isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah') ? 'Manage and edit homework' : 'Your assigned tasks'; ?></p>
    </div>

    <!-- TABLE -->
    <div class="overflow-x-auto">
      <table class="w-full text-sm text-left text-gray-700">
        <thead class="bg-gray-50 border-gray-300 border-b">
          <tr>
            <th class="px-8 py-3 font-medium">Title</th>
            <th class="px-8 py-3 font-medium text-right">Action</th>
          </tr>
        </thead>

        <?php $sql = "SELECT * from homework";
        $result = mysqli_query($con, $sql); ?>
        
        <tbody class="divide-y">

          <?php while($row = mysqli_fetch_assoc($result)) { ?>

            <tr class="hover:bg-gray-50 border-gray-300 transition">
              <td class="px-8 py-5 font-medium">
                <?php echo $row['title']; ?>
                <p class="text-xs text-gray-500 mt-1">Due: <?php echo $row['due_date']; ?></p>
              </td>
                <td class="px-8 py-5 text-right">
                  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah') { ?>
                      <div class="inline-flex items-center gap-2">
                        <a href="edit-work.php?id=<?php echo $row['id']; ?>">
                          <button class="px-4 py-1.5 text-sm rounded-full bg-amber-600 text-white hover:bg-amber-700 transition">
                            Edit
                          </button>
                        </a>
                        <button type="button" data-homework-delete-id="<?php echo $row['id']; ?>" class="px-4 py-1.5 text-sm rounded-full bg-red-600 text-white hover:bg-red-700 transition">
                          Delete
                        </button>
                      </div>
                  <?php } else {
                    // Fetch the submission status for this specific homework and student
                    $checkSubmission = mysqli_query($con, "SELECT status FROM student_homework_submissions WHERE homework_id = {$row['id']} AND student_id = {$_SESSION['id']}"); 
                    $submissionStatus = mysqli_fetch_assoc($checkSubmission);

                    if ($submissionStatus && $submissionStatus['status'] == 'submitted') { 
                  ?>
                      <button class="px-4 py-1.5 text-sm rounded-full bg-green-600 text-white cursor-default">
                        Submitted
                      </button>
                  <?php } else { ?>
                      <a href="view-work.php?id=<?php echo $row['id']; ?>">
                        <button class="px-4 py-1.5 text-sm rounded-full bg-blue-600 text-white hover:bg-blue-700 transition">
                          View
                        </button>
                      </a>
                  <?php }
                  } ?>
              </td>
            </tr>

          <?php } ?>
        </tbody>
      </table>
    </div>

  </div>

  <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah') { ?>
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
</script>

</body>
<script src="https://cdn.jsdelivr.net/npm/flowbite@4.0.1/dist/flowbite.min.js"></script>
</html>
