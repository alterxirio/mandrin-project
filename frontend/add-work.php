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

    <div class="max-w-5xl mx-auto px-6 py-8 space-y-10">
        
        <section class="bg-white shadow-md rounded-2xl border border-gray-200 p-8 space-y-6">
            <header class="space-y-2">
                <h1 class="text-2xl font-semibold text-gray-800">Maklumat Kerja Rumah</h1>
                <p class="text-gray-500">Masukkan maklumat asas tugasan sebelum menambah soalan.</p>
            </header>

            <div class="space-y-4">
                <div>
                    <label for="homeworkName" class="block text-sm font-medium text-gray-700 mb-1">Nama Kerja Rumah</label>
                    <input id="homeworkName" type="text" placeholder="Contoh: Latihan Bab 3"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="classSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                    <select id="classSelect"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">-- Pilih Kelas --</option>
                        <option value="1A">Kelas 1A</option>
                        <option value="1B">Kelas 1B</option>
                        <option value="2A">Kelas 2A</option>
                        <option value="2B">Kelas 2B</option>
                    </select>
                </div>

                <div>
                    <label for="dueDate" class="block text-sm font-medium text-gray-700 mb-1">Tarikh Akhir</label>
                    <input id="dueDate" type="date"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>
        </section>

        <div id="formContainer" class="space-y-6"></div>

        <section class="flex flex-col items-center space-y-4">
            <p class="text-lg font-medium text-gray-700">Sila pilih jenis soalan yang ingin dipilih</p>
            
            <div class="w-full bg-white shadow-md rounded-2xl border border-gray-200 flex justify-center gap-6 p-6">
                <button onclick="loadForm('drag-drop')" class="w-24 hover:scale-110 transition duration-200">
                    <img src="../media/graphic/drag.png" alt="Drag Drop" class="w-full object-contain">
                </button>

                <button onclick="loadForm('audio-image')" class="w-24 hover:scale-110 transition duration-200">
                    <img src="../media/graphic/hear.png" alt="Audio Image" class="w-full object-contain">
                </button>

                <button onclick="loadForm('match-image')" class="w-24 hover:scale-110 transition duration-200">
                    <img src="../media/graphic/match.png" alt="Match" class="w-full object-contain">
                </button>

                <button onclick="loadForm('mcq-text')" class="w-24 hover:scale-110 transition duration-200">
                    <img src="../media/graphic/mcq.png" alt="MCQ" class="w-full object-contain">
                </button>

                <button onclick="loadForm('true-false')" class="w-24 hover:scale-110 transition duration-200">
                    <img src="../media/graphic/true-false.png" alt="True False" class="w-full object-contain">
                </button>
            </div>
        </section>

        <div class="pt-4">
            <button id="submitAllQuestions" type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-4 text-base font-bold text-white hover:bg-red-700 shadow-lg transition disabled:opacity-60">
                Hantar Semua Soalan
            </button>
            <p id="submitStatus" class="mt-3 text-center text-sm text-gray-600"></p>
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
                wrapper.className = "question-wrapper relative border border-gray-200 rounded-2xl bg-white shadow-sm overflow-hidden mb-6";
                wrapper.dataset.questionType = type;

                const actions = document.createElement("div");
                actions.className = "flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-gray-50";

                const formLabel = document.createElement("p");
                formLabel.className = "text-sm font-bold text-gray-700 uppercase tracking-wide";
                formLabel.textContent = formTitles[type] ?? "Question Form";

                const removeButton = document.createElement("button");
                removeButton.type = "button";
                removeButton.className = "rounded-full bg-red-50 w-8 h-8 flex items-center justify-center text-red-600 hover:bg-red-600 hover:text-white transition";
                removeButton.innerHTML = "&times;";
                removeButton.addEventListener("click", () => { wrapper.remove(); });

                actions.appendChild(formLabel);
                actions.appendChild(removeButton);

                const content = document.createElement("div");
                content.className = "p-6";
                content.innerHTML = data;
                content.querySelectorAll("footer").forEach((footer) => footer.remove());

                wrapper.appendChild(actions);
                wrapper.appendChild(content);

                document.getElementById("formContainer").appendChild(wrapper);
                initQuestion(type, content);
            });
    }

    function collectQuestions(formData) {
        // Note: I changed the selector to look for the wrapper specifically
        const wrappers = Array.from(document.querySelectorAll("#formContainer .question-wrapper"));
        const questions = [];

        wrappers.forEach((wrapper, qIdx) => {
            const type = wrapper.dataset.questionType;
            const qData = { type: type };

            // 1. MCQ TEXT
            if (type === "mcq-text") {
                // Use class-based selectors or search within the wrapper only
                qData.question_text = wrapper.querySelector('#mcqQuestion')?.value?.trim() || "";
                const choiceRows = wrapper.querySelectorAll('#mcqChoicesGrid > div');
                qData.choices = Array.from(choiceRows).map(row => ({
                    text: row.querySelector("input[type='text']")?.value || "",
                    is_correct: row.querySelector("input[type='radio']")?.checked || false
                }));
            } 
            
            // 2. AUDIO + IMAGE
            else if (type === "audio-image") {
                qData.question_text = wrapper.querySelector("#instruction")?.value || "Dengar dan pilih.";
                const audioInput = wrapper.querySelector("input[type='file'][accept*='audio']");
                if (audioInput?.files[0]) {
                    const key = `q_${qIdx}_audio`;
                    formData.append(key, audioInput.files[0]);
                    qData.audio_key = key;
                }
                const choiceCards = wrapper.querySelectorAll('#choicesGrid > div');
                qData.choices = Array.from(choiceCards).map((card, cIdx) => {
                    const choice = {
                        label: card.querySelector("input[type='text']")?.value || "",
                        is_correct: card.querySelector("input[type='radio']")?.checked || false
                    };
                    const imgInput = card.querySelector("input[type='file']");
                    if (imgInput?.files[0]) {
                        const imgKey = `q_${qIdx}_c_${cIdx}_img`;
                        formData.append(imgKey, imgInput.files[0]);
                        choice.image_key = imgKey;
                    }
                    return choice;
                });
            }

            // 3. MATCH IMAGE
            else if (type === "match-image") {
                qData.question_text = wrapper.querySelector('#matchInstruction')?.value?.trim() || 'Padankan perkataan dengan gambar yang betul.';
                const pairRows = wrapper.querySelectorAll('#matchPairList > div');
                qData.pairs = Array.from(pairRows).map((row, pIdx) => {
                    const pair = {
                        word: row.querySelector("input[type='text']")?.value?.trim() || ""
                    };
                    const imgInput = row.querySelector("input[type='file']");
                    if (imgInput?.files[0]) {
                        const imgKey = `q_${qIdx}_p_${pIdx}_img`;
                        formData.append(imgKey, imgInput.files[0]);
                        pair.image_key = imgKey;
                    }
                    return pair;
                });
            }

            // 4. TRUE / FALSE
            else if (type === "true-false") {
                qData.question_text = wrapper.querySelector('#tfQuestion')?.value?.trim() || '';
                qData.correct_answer = wrapper.querySelector("input[type='radio']:checked")?.value || null;
                
                const imgInput = wrapper.querySelector("input[type='file']");
                if (imgInput?.files?.[0]) {
                    const key = `q_${qIdx}_tf_image`;
                    formData.append(key, imgInput.files[0]);
                    qData.image_key = key;
                }
            }

            // 5. DRAG & DROP
            else if (type === "drag-drop") {
                const instruction = wrapper.querySelector('#instruction')?.value?.trim() || 'Susun perkataan ini menjadi ayat yang betul.';
                const words = Array.from(wrapper.querySelectorAll('#wordInputs .word-input'))
                    .map(input => input.value.trim())
                    .filter(Boolean);

                qData.question_text = instruction;
                qData.words = words;
            }

            questions.push(qData);
        });
        return questions;
    }

      document.getElementById("submitAllQuestions").addEventListener("click", async () => {
          const submitButton = document.getElementById("submitAllQuestions");
          const submitStatus = document.getElementById("submitStatus");
          
          // 1. Grab Top Info values
          const hwName = document.getElementById("homeworkName").value.trim();
          const classId = document.getElementById("classSelect").value;
          const dueDate = document.getElementById("dueDate").value;

          // 2. Validation
          if (!hwName || !classId || !dueDate) {
              alert("Sila lengkapkan maklumat kerja rumah (Nama, Kelas, dan Tarikh).");
              return;
          }

          const formData = new FormData();
          const questions = collectQuestions(formData);

          if (questions.length === 0) {
              alert("Tambah sekurang-kurangnya satu soalan sebelum hantar.");
              return;
          }

          submitButton.disabled = true;
          submitStatus.textContent = "Sedang menghantar...";

          // 3. Append Top Info to FormData
          formData.append("homework_name", hwName);
          formData.append("class_id", classId);
          formData.append("due_date", dueDate);
          
          // 4. Append the JSON string of questions
          formData.append("questions", JSON.stringify(questions));

          try {
              const response = await fetch("../backend/homeworkBE.php", {
                  method: "POST",
                  body: formData,
              });
              const result = await response.json();
              if (!response.ok || !result.success) throw new Error(result.message || "Gagal hantar.");
              
              submitStatus.textContent = "Berjaya dihantar!";
              window.location.href = "work.php";
          } catch (error) {
              submitStatus.textContent = error.message;
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
  
</body>
</html>