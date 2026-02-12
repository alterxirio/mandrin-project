<!DOCTYPE html>
<html lang="en">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>Add Work</title>

      <link rel="stylesheet" href="../css/work.php">
      <?php include("header.php"); ?>
  </head>

  <body class="bg-gray-100 min-h-screen">
  <?php include("navbar.php"); ?>

  <div class="grid place-items-center bg-gray-100">

  <p class="mb-3 text-lg">Sila pilih jenis soalan yang ingin dipilih</p>

    <div class="w-[65vw] bg-white shadow-lg rounded-2xl border border-gray-200 flex justify-center gap-4 p-4">

        <button onclick="loadForm('drag-drop')" class="w-[15%] hover:scale-105 transition">
          <img src="../media/graphic/drag.png" class="w-full object-contain">
        </button>

        <button onclick="loadForm('audio-image')" class="w-[15%] hover:scale-105 transition">
          <img src="../media/graphic/hear.png" class="w-full object-contain">
        </button>

        <button onclick="loadForm('match-image')" class="w-[15%] hover:scale-105 transition">
          <img src="../media/graphic/match.png" class="w-full object-contain">
        </button>

        <button onclick="loadForm('mcq-text')" class="w-[15%] hover:scale-105 transition">
          <img src="../media/graphic/mcq.png" class="w-full object-contain">
        </button>

        <button onclick="loadForm('true-false')" class="w-[15%] hover:scale-105 transition">
          <img src="../media/graphic/true-false.png" class="w-full object-contain">
        </button>

    </div>
  </div>

  <!-- Forms will appear here -->
  <div id="formContainer" class="max-w-6xl mx-auto px-6 py-8 space-y-6"></div>

  <script>
    function loadForm(type) {
        fetch("form-loader.php?type=" + type)
          .then(response => response.text())
          .then(data => {
              document.getElementById("formContainer").innerHTML += data;
          });
    }
  </script>

  </body>
</html>
