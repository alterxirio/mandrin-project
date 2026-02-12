<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Word Page</title>

    <link rel="stylesheet" href="../css/work.php">
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

<?php
  $question_type = $_GET['type'] ?? '';
  $question_map = [
    'drag-drop' => 'question-forms/drag-drop.php',
    'audio-image' => 'question-forms/audio-image.php',
    'match-image' => 'question-forms/match-image.php',
    'mcq-text' => 'question-forms/mcq-text.php',
    'true-false' => 'question-forms/true-false-image.php',
  ];
?>

<div class="grid place-items-center bg-gray-100">

<p class="mb-3 text-lg">Sila pilih jenis soalan yang ingin dipilih</p>

  <div class="w-[65vw] bg-white shadow-lg rounded-2xl border border-gray-200 flex justify-center gap-4 p-4">
  
    <a class="w-[15%] hover:scale-105 transition" href="add-work.php?type=drag-drop">
      <img src="../media/graphic/drag.png" alt="Soalan seret dan lepas" class="w-full object-contain">
    </a>

    <a class="w-[15%] hover:scale-105 transition" href="add-work.php?type=audio-image">
      <img src="../media/graphic/hear.png" alt="Soalan dengar dan pilih gambar" class="w-full object-contain">
    </a>

    <a class="w-[15%] hover:scale-105 transition" href="add-work.php?type=match-image">
      <img src="../media/graphic/match.png" alt="Soalan padankan perkataan dan gambar" class="w-full object-contain">
    </a>

    <a class="w-[15%] hover:scale-105 transition" href="add-work.php?type=mcq-text">
      <img src="../media/graphic/mcq.png" alt="Soalan MCQ teks sahaja" class="w-full object-contain">
    </a>

    <a class="w-[15%] hover:scale-105 transition" href="add-work.php?type=true-false">
      <img src="../media/graphic/true-false.png" alt="Soalan betul salah berdasarkan gambar" class="w-full object-contain">
    </a>

  </div>

</div>

<?php if ($question_type && array_key_exists($question_type, $question_map)) { ?>
  <div class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
      <form action="#" method="post" enctype="multipart/form-data" class="space-y-6">
        <input type="hidden" name="question_type" value="<?php echo htmlspecialchars($question_type); ?>">
        <?php include($question_map[$question_type]); ?>
      </form>
    </div>
  </div>
<?php } ?>



</body>

</html>
