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

  <div id="formContainer" class="w-full max-w-6xl mx-auto px-6 py-8 space-y-6"></div>

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


  <script>
  const formTitles = {
  "drag-drop": "Drag & Drop",
  "audio-image": "Audio + Image",
  "match-image": "Match Image",
  "mcq-text": "MCQ Text",
  "true-false": "True / False",
  };

   function loadForm(type) {
    fetch("form-loader.php?type=" + type)
      .then(response => response.text())
      .then(data => {

          const wrapper = document.createElement("section");
          wrapper.className = "relative border border-gray-200 rounded-2xl bg-gray-50";

          const actions = document.createElement("div");
          actions.className = "sticky top-0 z-10 flex items-center justify-between px-4 py-3 border-b border-gray-200 bg-white rounded-t-2xl";

          const formLabel = document.createElement("p");
          formLabel.className = "text-sm font-medium text-gray-600";
          formLabel.textContent = formTitles[type] ?? "Question Form";

          const removeButton = document.createElement("button");
          removeButton.type = "button";
          removeButton.className = "rounded-md border border-red-300 px-3 py-1 text-sm font-medium text-red-600 hover:bg-red-50";
          removeButton.textContent = "x";
          removeButton.addEventListener("click", () => {
            wrapper.remove();
          });

          actions.appendChild(formLabel);
          actions.appendChild(removeButton);

          const content = document.createElement("div");
          content.innerHTML = data;

          wrapper.appendChild(actions);
          wrapper.appendChild(content);

          document.getElementById("formContainer")
                  .appendChild(wrapper);

           initQuestion(type, content);
      });
    }
  </script>

  <script src="../js/drag-drop.js"></script>
  <script src="../js/mcq-text.js"></script>
  <script src="../js/audio-image.js"></script>
  <script src="../js/match-image.js"></script>
  <script src="../js/true-false.js"></script>
  <script src="../js/initQuestion.js"></script>
  <script src="../js/question-init.js"></script>
  
  </body>
</html>
