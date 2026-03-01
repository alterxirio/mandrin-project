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

    <div class="w-full max-w-6xl mx-auto px-6 pb-8">
      <button id="submitAllQuestions" type="button" class="w-full inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-3 text-sm font-semibold text-white hover:bg-red-700 disabled:opacity-60 disabled:cursor-not-allowed">
        Hantar Semua Soalan
      </button>
      <p id="submitStatus" class="mt-3 text-sm text-gray-600"></p>
    </div>

  </div>

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
          wrapper.className = "question-wrapper relative border border-gray-200 rounded-2xl bg-gray-50";
          wrapper.dataset.questionType = type;

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

          content.querySelectorAll("footer").forEach((footer) => footer.remove());

          wrapper.appendChild(actions);
          wrapper.appendChild(content);

          document.getElementById("formContainer").appendChild(wrapper);
          initQuestion(type, content);
        });
    }

    function collectQuestions(formData) {
      const wrappers = Array.from(document.querySelectorAll("#formContainer .question-wrapper"));
      const questions = [];

      wrappers.forEach((wrapper, formIndex) => {
        const type = wrapper.dataset.questionType;
        const question = { type };

        if (type === "mcq-text") {
          question.question_text = wrapper.querySelector("#mcqQuestion")?.value?.trim() || "";
          question.choices = Array.from(wrapper.querySelectorAll("#mcqChoicesGrid > div")).map((card, choiceIndex) => ({
            text: card.querySelector("input[type='text']")?.value?.trim() || "",
            is_correct: Boolean(card.querySelector("input[type='radio']")?.checked),
            index: choiceIndex,
          }));
        }

        if (type === "true-false") {
          question.question_text = wrapper.querySelector("#tfQuestion")?.value?.trim() || "";
          question.correct_answer = wrapper.querySelector("input[name='tfCorrectAnswer']:checked")?.value || "";
          const imageInput = wrapper.querySelector("#tfImageUpload");
          if (imageInput?.files?.[0]) {
            const key = `q_${formIndex}_tf_image`;
            formData.append(key, imageInput.files[0]);
            question.image_key = key;
          }
        }

        if (type === "audio-image") {
          question.question_text = wrapper.querySelector("#instruction")?.value?.trim() || "";

          const audioInput = wrapper.querySelector("#audioUpload");
          if (audioInput?.files?.[0]) {
            const key = `q_${formIndex}_audio`;
            formData.append(key, audioInput.files[0]);
            question.audio_key = key;
          }

          question.choices = Array.from(wrapper.querySelectorAll("#choicesGrid > div")).map((card, choiceIndex) => {
            const data = {
              label: card.querySelector("input[type='text']")?.value?.trim() || "",
              is_correct: Boolean(card.querySelector("input[type='radio']")?.checked),
              index: choiceIndex,
            };

            const imageInput = card.querySelector("input[type='file']");
            if (imageInput?.files?.[0]) {
              const key = `q_${formIndex}_choice_${choiceIndex}_image`;
              formData.append(key, imageInput.files[0]);
              data.image_key = key;
            }

            return data;
          });
        }

        if (type === "match-image") {
          question.question_text = wrapper.querySelector("#matchInstruction")?.value?.trim() || "";
          question.pairs = Array.from(wrapper.querySelectorAll("#matchPairList > div")).map((row, pairIndex) => {
            const wordInput = row.querySelector("input[type='text']");
            const imageInput = row.querySelector("input[type='file']");
            const pair = {
              word: wordInput?.value?.trim() || "",
              index: pairIndex,
            };

            if (imageInput?.files?.[0]) {
              const key = `q_${formIndex}_pair_${pairIndex}_image`;
              formData.append(key, imageInput.files[0]);
              pair.image_key = key;
            }

            return pair;
          });
        }

        if (type === "drag-drop") {
          question.question_text = wrapper.querySelector("#instruction")?.value?.trim() || "";
          question.words = Array.from(wrapper.querySelectorAll("#wordInputs .word-input"))
            .map((input) => input.value.trim())
            .filter(Boolean);
          question.correct_answer = wrapper.querySelector("#answerSentence")?.value?.trim() || question.words.join(" ");
        }

        questions.push(question);
      });

      return questions;
    }

    document.getElementById("submitAllQuestions").addEventListener("click", async () => {
      const submitButton = document.getElementById("submitAllQuestions");
      const submitStatus = document.getElementById("submitStatus");
      const formData = new FormData();

      const questions = collectQuestions(formData);

      if (questions.length === 0) {
        submitStatus.textContent = "Tambah sekurang-kurangnya satu soalan sebelum hantar.";
        return;
      }

      submitButton.disabled = true;
      submitStatus.textContent = "Sedang menghantar semua soalan...";

      formData.append("questions", JSON.stringify(questions));

      try {
        const response = await fetch("../backend/homeworkBE.php", {
          method: "POST",
          body: formData,
        });

        const result = await response.json();

        if (!response.ok || !result.success) {
          throw new Error(result.message || "Gagal hantar soalan.");
        }

        submitStatus.textContent = "Semua soalan berjaya dihantar.";
        window.location.href = "work.php";
      } catch (error) {
        submitStatus.textContent = error.message;
      } finally {
        submitButton.disabled = false;
      }
    });
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
