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

<?php
  $question_type = $_GET['type'] ?? '';
  $question_map = [
    'drag-drop' => 'question-forms/drag-drop.php',
    'audio-image' => 'question-forms/audio-image.php',
    'match-image' => 'question-forms/match-image.php',
    'mcq-text' => 'question-forms/mcq-text.php',
    'true-false' => 'question-forms/true-false-image.php',
  ];

  $question_labels = [
    'drag-drop' => 'Seret dan Lepas',
    'audio-image' => 'Dengar & Pilih Gambar',
    'match-image' => 'Padan Perkataan & Gambar',
    'mcq-text' => 'MCQ (Teks)',
    'true-false' => 'Betul / Salah (Gambar)',
  ];
?>

<div class="max-w-6xl mx-auto px-6 py-6">
  <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-4">
    <div class="flex items-center justify-between">
      <h3 class="text-lg font-semibold text-gray-800">Senarai Soalan Dalam Tugasan</h3>
      <p class="text-sm text-gray-500">Klik ikon soalan di bawah untuk tambah</p>
    </div>

    <form id="questionPlanForm" action="#" method="post" class="space-y-4">
      <div id="selectedQuestionList" class="space-y-2"></div>
      <p id="selectedQuestionEmpty" class="text-sm text-gray-400">Belum ada soalan dipilih.</p>
      <p class="text-xs text-gray-500">Senarai ini disimpan automatik semasa anda pilih jenis soalan.</p>
    </form>
  </div>
</div>

<div class="grid place-items-center bg-gray-100">

<p class="mb-3 text-lg">Sila pilih jenis soalan yang ingin dipilih</p>

  <div class="w-[65vw] bg-white shadow-lg rounded-2xl border border-gray-200 flex justify-center gap-4 p-4">
  
    <a class="question-type-link w-[15%] hover:scale-105 transition" data-type="drag-drop" href="add-work.php?type=drag-drop">
      <img src="../media/graphic/drag.png" alt="Soalan seret dan lepas" class="w-full object-contain">
    </a>

    <a class="question-type-link w-[15%] hover:scale-105 transition" data-type="audio-image" href="add-work.php?type=audio-image">
      <img src="../media/graphic/hear.png" alt="Soalan dengar dan pilih gambar" class="w-full object-contain">
    </a>

    <a class="question-type-link w-[15%] hover:scale-105 transition" data-type="match-image" href="add-work.php?type=match-image">
      <img src="../media/graphic/match.png" alt="Soalan padankan perkataan dan gambar" class="w-full object-contain">
    </a>

    <a class="question-type-link w-[15%] hover:scale-105 transition" data-type="mcq-text" href="add-work.php?type=mcq-text">
      <img src="../media/graphic/mcq.png" alt="Soalan MCQ teks sahaja" class="w-full object-contain">
    </a>

    <a class="question-type-link w-[15%] hover:scale-105 transition" data-type="true-false" href="add-work.php?type=true-false">
      <img src="../media/graphic/true-false.png" alt="Soalan betul salah berdasarkan gambar" class="w-full object-contain">
    </a>

  </div>

</div>

<div class="max-w-6xl mx-auto px-6 pb-8">
  <div id="questionFormPanel" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8 hidden">
    <form action="#" method="post" enctype="multipart/form-data" class="space-y-6">
      <input id="activeQuestionType" type="hidden" name="question_type" value="">

      <?php foreach ($question_map as $type_key => $question_file) { ?>
        <div class="question-form-content hidden" data-form-type="<?php echo htmlspecialchars($type_key); ?>">
          <?php include($question_file); ?>
        </div>
      <?php } ?>
    </form>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const selectedTypesStorageKey = "selected-question-types";
    const questionLabels = <?php echo json_encode($question_labels); ?>;
    const typeLinks = document.querySelectorAll(".question-type-link");
    const selectedList = document.getElementById("selectedQuestionList");
    const emptyText = document.getElementById("selectedQuestionEmpty");
    const questionPlanForm = document.getElementById("questionPlanForm");
    const questionFormPanel = document.getElementById("questionFormPanel");
    const questionTypeField = document.getElementById("activeQuestionType");
    const questionFormSections = document.querySelectorAll(".question-form-content");

    if (!selectedList || !emptyText || !questionPlanForm || !questionFormPanel || !questionTypeField) {
      return;
    }

    const showQuestionForm = (typeKey) => {
      let isShown = false;

      questionFormSections.forEach((section) => {
        const matches = section.dataset.formType === typeKey;
        section.classList.toggle("hidden", !matches);
        if (matches) {
          isShown = true;
        }
      });

      questionFormPanel.classList.toggle("hidden", !isShown);
      questionTypeField.value = isShown ? typeKey : "";
    };

    const updateEmptyState = () => {
      emptyText.classList.toggle("hidden", selectedList.children.length > 0);
    };

    const refreshOrderInputs = () => {
      Array.from(selectedList.children).forEach((item, index) => {
        const orderInput = item.querySelector("input[name='question_orders[]']");
        const choiceInput = item.querySelector("input[name='question_formats[]']");
        if (orderInput) {
          orderInput.value = index + 1;
        }

        if (choiceInput) {
          choiceInput.dataset.order = index + 1;
        }
      });
    };

    const getSelectedTypes = () => {
      return Array.from(
        selectedList.querySelectorAll("input[name='question_formats[]']"),
        (input) => input.value
      );
    };

    const persistSelectedTypes = () => {
      localStorage.setItem(selectedTypesStorageKey, JSON.stringify(getSelectedTypes()));
    };

    const createSelectedItem = (typeKey) => {
      const wrapper = document.createElement("div");
      wrapper.className = "flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2";

      const label = document.createElement("p");
      label.className = "text-sm text-gray-700";
      label.textContent = questionLabels[typeKey] || typeKey;

      const rightGroup = document.createElement("div");
      rightGroup.className = "flex items-center gap-3";

      const hidden = document.createElement("input");
      hidden.type = "hidden";
      hidden.name = "question_formats[]";
      hidden.value = typeKey;

      const orderHidden = document.createElement("input");
      orderHidden.type = "hidden";
      orderHidden.name = "question_orders[]";
      orderHidden.value = selectedList.children.length + 1;

      const removeBtn = document.createElement("button");
      removeBtn.type = "button";
      removeBtn.className = "text-xs font-semibold text-red-600 hover:text-red-700";
      removeBtn.textContent = "Buang";
      removeBtn.addEventListener("click", () => {
        wrapper.remove();
        refreshOrderInputs();
        updateEmptyState();
        persistSelectedTypes();
      });

      rightGroup.appendChild(hidden);
      rightGroup.appendChild(orderHidden);
      rightGroup.appendChild(removeBtn);
      wrapper.appendChild(label);
      wrapper.appendChild(rightGroup);

      return wrapper;
    };

    typeLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();
        const typeKey = link.dataset.type;
        if (!typeKey) {
          return;
        }

        selectedList.appendChild(createSelectedItem(typeKey));
        refreshOrderInputs();
        updateEmptyState();
        persistSelectedTypes();

        window.history.replaceState({}, "", link.href);
        showQuestionForm(typeKey);
      });
    });

    questionPlanForm.addEventListener("submit", (event) => {
      event.preventDefault();
    });

    document.querySelectorAll("form[action='#']").forEach((form) => {
      form.addEventListener("submit", (event) => {
        event.preventDefault();
      });
    });

    const restoreSelectedTypes = () => {
      const raw = localStorage.getItem(selectedTypesStorageKey);

      if (!raw) {
        return;
      }

      let parsedTypes = [];
      try {
        parsedTypes = JSON.parse(raw);
      } catch (_error) {
        localStorage.removeItem(selectedTypesStorageKey);
        return;
      }

      if (!Array.isArray(parsedTypes)) {
        localStorage.removeItem(selectedTypesStorageKey);
        return;
      }

      parsedTypes.forEach((typeKey) => {
        if (questionLabels[typeKey]) {
          selectedList.appendChild(createSelectedItem(typeKey));
        }
      });

      refreshOrderInputs();
    };

    restoreSelectedTypes();

    if (questionLabels[<?php echo json_encode($question_type); ?>]) {
      showQuestionForm(<?php echo json_encode($question_type); ?>);
    }

    updateEmptyState();
  });
</script>

</body>

</html>
