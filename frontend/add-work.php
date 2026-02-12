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
  $question_labels = [
    'drag-drop' => 'Seret dan Lepas',
    'audio-image' => 'Dengar & Pilih Gambar',
    'match-image' => 'Padan Perkataan & Gambar',
    'mcq-text' => 'MCQ (Teks)',
    'true-false' => 'Betul / Salah (Gambar)',
  ];
?>

<div class="max-w-6xl mx-auto px-6 py-6 space-y-6">
  <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-4">
    <div class="flex items-center justify-between gap-3">
      <h3 class="text-lg font-semibold text-gray-800">Senarai Soalan Dalam Tugasan</h3>
      <p class="text-sm text-gray-500">Pilih ikon untuk tambah soalan</p>
    </div>

    <div id="selectedQuestionList" class="space-y-2"></div>
    <p id="selectedQuestionEmpty" class="text-sm text-gray-400">Belum ada soalan. Klik ikon di bawah untuk mula.</p>
  </div>

  <div class="bg-white shadow-lg rounded-2xl border border-gray-200 p-4">
    <p class="mb-3 text-lg">Pilih format soalan</p>

    <div class="flex flex-wrap justify-center gap-4">
      <a class="question-type-link w-[120px] hover:scale-105 transition" data-type="drag-drop" href="add-work.php?type=drag-drop">
        <img src="../media/graphic/drag.png" alt="Soalan seret dan lepas" class="w-full object-contain">
      </a>

      <a class="question-type-link w-[120px] hover:scale-105 transition" data-type="audio-image" href="add-work.php?type=audio-image">
        <img src="../media/graphic/hear.png" alt="Soalan dengar dan pilih gambar" class="w-full object-contain">
      </a>

      <a class="question-type-link w-[120px] hover:scale-105 transition" data-type="match-image" href="add-work.php?type=match-image">
        <img src="../media/graphic/match.png" alt="Soalan padankan perkataan dan gambar" class="w-full object-contain">
      </a>

      <a class="question-type-link w-[120px] hover:scale-105 transition" data-type="mcq-text" href="add-work.php?type=mcq-text">
        <img src="../media/graphic/mcq.png" alt="Soalan MCQ teks sahaja" class="w-full object-contain">
      </a>

      <a class="question-type-link w-[120px] hover:scale-105 transition" data-type="true-false" href="add-work.php?type=true-false">
        <img src="../media/graphic/true-false.png" alt="Soalan betul salah berdasarkan gambar" class="w-full object-contain">
      </a>
    </div>
  </div>

  <div id="questionEditorPanel" class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 hidden">
    <div class="flex items-center justify-between mb-4">
      <h4 id="activeFormTitle" class="text-lg font-semibold text-gray-800"></h4>
      <span class="text-xs text-gray-500">Isi borang untuk soalan ini</span>
    </div>
    <div id="questionEditorBody"></div>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const questionLabels = <?php echo json_encode($question_labels); ?>;
    const typeLinks = document.querySelectorAll(".question-type-link");
    const selectedList = document.getElementById("selectedQuestionList");
    const emptyText = document.getElementById("selectedQuestionEmpty");
    const editorPanel = document.getElementById("questionEditorPanel");
    const editorBody = document.getElementById("questionEditorBody");
    const activeFormTitle = document.getElementById("activeFormTitle");

    if (!selectedList || !emptyText || !editorPanel || !editorBody || !activeFormTitle) {
      return;
    }

    const selectedQuestions = [];
    let activeQuestionId = null;
    let sequence = 1;
    const formHtmlCache = {};

    const updateEmptyState = () => {
      emptyText.classList.toggle("hidden", selectedQuestions.length > 0);
    };

    const executeEmbeddedScripts = (container) => {
      const scripts = container.querySelectorAll("script");
      scripts.forEach((oldScript) => {
        const newScript = document.createElement("script");
        if (oldScript.src) {
          newScript.src = oldScript.src;
        } else {
          newScript.textContent = oldScript.textContent;
        }
        oldScript.replaceWith(newScript);
      });
    };

    const renderQuestionList = () => {
      selectedList.innerHTML = "";

      selectedQuestions.forEach((question, index) => {
        const row = document.createElement("div");
        row.className = "flex items-center justify-between rounded-lg border border-gray-200 px-4 py-2";

        const label = document.createElement("button");
        label.type = "button";
        label.className = "text-sm text-left text-gray-700 hover:text-red-600";
        label.textContent = `${index + 1}. ${questionLabels[question.type] || question.type}`;
        label.addEventListener("click", () => {
          activeQuestionId = question.id;
          renderQuestionList();
          renderEditor();
        });

        const right = document.createElement("div");
        right.className = "flex items-center gap-3";

        const orderInput = document.createElement("input");
        orderInput.type = "hidden";
        orderInput.name = "question_orders[]";
        orderInput.value = index + 1;

        const formatInput = document.createElement("input");
        formatInput.type = "hidden";
        formatInput.name = "question_formats[]";
        formatInput.value = question.type;

        const removeBtn = document.createElement("button");
        removeBtn.type = "button";
        removeBtn.className = "text-xs font-semibold text-red-600 hover:text-red-700";
        removeBtn.textContent = "Buang";
        removeBtn.addEventListener("click", () => {
          const targetIndex = selectedQuestions.findIndex((item) => item.id === question.id);
          if (targetIndex >= 0) {
            selectedQuestions.splice(targetIndex, 1);
          }

          if (activeQuestionId === question.id) {
            activeQuestionId = selectedQuestions.length ? selectedQuestions[selectedQuestions.length - 1].id : null;
          }

          renderQuestionList();
          renderEditor();
        });

        if (question.id === activeQuestionId) {
          row.classList.add("ring-1", "ring-red-400");
        }

        right.appendChild(orderInput);
        right.appendChild(formatInput);
        right.appendChild(removeBtn);

        row.appendChild(label);
        row.appendChild(right);

        selectedList.appendChild(row);
      });

      updateEmptyState();
    };

    const loadQuestionForm = async (typeKey) => {
      if (formHtmlCache[typeKey]) {
        return formHtmlCache[typeKey];
      }

      const response = await fetch(`form-loader.php?type=${encodeURIComponent(typeKey)}`);
      if (!response.ok) {
        throw new Error("Gagal memuatkan borang soalan.");
      }

      const html = await response.text();
      formHtmlCache[typeKey] = html;
      return html;
    };

    const renderEditor = async () => {
      const activeQuestion = selectedQuestions.find((item) => item.id === activeQuestionId);

      if (!activeQuestion) {
        editorPanel.classList.add("hidden");
        editorBody.innerHTML = "";
        activeFormTitle.textContent = "";
        return;
      }

      activeFormTitle.textContent = questionLabels[activeQuestion.type] || activeQuestion.type;
      editorPanel.classList.remove("hidden");
      editorBody.innerHTML = '<p class="text-sm text-gray-500">Memuatkan borang...</p>';

      try {
        const html = await loadQuestionForm(activeQuestion.type);
        editorBody.innerHTML = html;
        executeEmbeddedScripts(editorBody);
      } catch (_error) {
        editorBody.innerHTML = '<p class="text-sm text-red-600">Borang tidak dapat dipaparkan. Sila cuba lagi.</p>';
      }
    };

    typeLinks.forEach((link) => {
      link.addEventListener("click", (event) => {
        event.preventDefault();

        const typeKey = link.dataset.type;
        if (!typeKey || !questionLabels[typeKey]) {
          return;
        }

        selectedQuestions.push({
          id: `q-${sequence}`,
          type: typeKey,
        });

        sequence += 1;
        activeQuestionId = selectedQuestions[selectedQuestions.length - 1].id;

        renderQuestionList();
        renderEditor();
      });
    });

    renderQuestionList();
  });
</script>

</body>

</html>
