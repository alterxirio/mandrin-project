<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    header('Location: work.php');
    exit;
}

$homeworkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$homework = null;
$questions = [];

if ($homeworkId > 0) {
    $hwStmt = mysqli_prepare($con, 'SELECT id, title, class, due_date FROM homework WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($hwStmt, 'i', $homeworkId);
    mysqli_stmt_execute($hwStmt);
    $homeworkResult = mysqli_stmt_get_result($hwStmt);
    $homework = mysqli_fetch_assoc($homeworkResult);

    $questionStmt = mysqli_prepare(
        $con,
        'SELECT id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file
         FROM questions
         WHERE homework_id = ?
         ORDER BY id ASC'
    );
    mysqli_stmt_bind_param($questionStmt, 'i', $homeworkId);
    mysqli_stmt_execute($questionStmt);
    $questionResult = mysqli_stmt_get_result($questionStmt);

    while ($row = mysqli_fetch_assoc($questionResult)) {
        $frontendType = 'mcq-text';
        if ($row['type'] === 'listening') $frontendType = 'audio-image';
        elseif ($row['type'] === 'picture') $frontendType = 'match-image';
        elseif ($row['type'] === 'truefalse') $frontendType = 'true-false';
        elseif ($row['type'] === 'rearrange') $frontendType = 'drag-drop';

        $formatted = [
            'id' => (int)$row['id'],
            'type' => $frontendType,
            'question_text' => $row['question_text'] ?? '',
            'correct_answer' => $row['correct_answer'] ?? '',
            'audio_file' => $row['audio_file'] ?? '',
            'image_file' => $row['image_file'] ?? '',
            'audioImage_label' => $row['audioImage_label'] ?? '',
            'option_a' => $row['option_a'] ?? '',
            'option_b' => $row['option_b'] ?? '',
            'option_c' => $row['option_c'] ?? '',
            'option_d' => $row['option_d'] ?? '',
        ];

        $questions[] = $formatted;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Work</title>
    <link rel="stylesheet" href="../css/work.php">
    <?php include("header.php"); ?>
</head>

<body class="bg-gray-100 min-h-screen">
    <?php include("navbar.php"); ?>

    <div class="max-w-5xl mx-auto px-6 py-8 space-y-10">
        <?php if (!$homework): ?>
            <section class="bg-white shadow-md rounded-2xl border border-gray-200 p-8 text-center space-y-3">
                <h1 class="text-xl font-semibold text-gray-800">Kerja rumah tidak ditemui</h1>
                <a href="work.php" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Kembali</a>
            </section>
        <?php else: ?>
        <section class="bg-white shadow-md rounded-2xl border border-gray-200 p-8 space-y-6">
            <header class="space-y-2">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Kerja Rumah</h1>
                <p class="text-gray-500">Kemaskini maklumat tugasan dan soalan sedia ada.</p>
            </header>

            <div class="space-y-4">
                <div>
                    <label for="homeworkName" class="block text-sm font-medium text-gray-700 mb-1">Nama Kerja Rumah</label>
                    <input id="homeworkName" type="text"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"
                           value="<?php echo htmlspecialchars($homework['title']); ?>">
                </div>

                <div>
                    <label for="classSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                    <select id="classSelect"
                            class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach (['1A','1B','2A','2B'] as $classOpt): ?>
                            <option value="<?php echo $classOpt; ?>" <?php echo ($homework['class'] === $classOpt) ? 'selected' : ''; ?>>Kelas <?php echo $classOpt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="dueDate" class="block text-sm font-medium text-gray-700 mb-1">Tarikh Akhir</label>
                    <input id="dueDate" type="date"
                           value="<?php echo htmlspecialchars($homework['due_date']); ?>"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>

        </section>

        <div id="formContainer" class="space-y-6"></div>

        <section class="flex flex-col items-center space-y-4">
            <p class="text-lg font-medium text-gray-700">Tambah soalan baharu jika perlu</p>
            <div class="w-full bg-white shadow-md rounded-2xl border border-gray-200 flex justify-center gap-6 p-6">
                <button onclick="loadForm('drag-drop')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/drag.png" alt="Drag Drop" class="w-full object-contain"></button>
                <button onclick="loadForm('audio-image')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/hear.png" alt="Audio Image" class="w-full object-contain"></button>
                <button onclick="loadForm('match-image')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/match.png" alt="Match" class="w-full object-contain"></button>
                <button onclick="loadForm('mcq-text')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/mcq.png" alt="MCQ" class="w-full object-contain"></button>
                <button onclick="loadForm('true-false')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/true-false.png" alt="True False" class="w-full object-contain"></button>
            </div>
        </section>

        <div class="pt-4">
            <button id="submitAllQuestions" type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-amber-600 px-5 py-4 text-base font-bold text-white hover:bg-amber-700 shadow-lg transition disabled:opacity-60">
                Simpan Perubahan
            </button>
            <p id="submitStatus" class="mt-3 text-center text-sm text-gray-600"></p>
        </div>
        <?php endif; ?>
    </div>

    <script>
    const existingQuestions = <?php echo json_encode($questions, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); ?>;
    const homeworkId = <?php echo (int)$homeworkId; ?>;
    const formTitles = {
        "drag-drop": "Drag & Drop",
        "audio-image": "Audio + Image",
        "match-image": "Match Image",
        "mcq-text": "MCQ Text",
        "true-false": "True / False",
    };

    async function loadForm(type) {
        const response = await fetch("form-loader.php?type=" + type);
        const data = await response.text();

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

        actions.append(formLabel, removeButton);

        const content = document.createElement("div");
        content.className = "p-6";
        content.innerHTML = data;
        content.querySelectorAll("footer").forEach((footer) => footer.remove());

        wrapper.append(actions, content);
        document.getElementById("formContainer").appendChild(wrapper);
        initQuestion(type, content);

        return { wrapper, content };
    }

    function splitCsv(value) {
        if (!value) return [];
        return value.split(',').map(item => item.trim());
    }

    function showExistingMediaNote(container, text) {
        if (!text) return;
        const note = document.createElement('p');
        note.className = 'text-xs text-gray-500 mt-1';
        note.textContent = text;
        container.appendChild(note);
    }

    function setExistingImagePreview(previewBox, imagePath) {
        if (!previewBox || !imagePath) return;

        previewBox.textContent = '';
        previewBox.style.backgroundImage = `url('../${imagePath}')`;
        previewBox.style.backgroundSize = 'cover';
        previewBox.style.backgroundPosition = 'center';
        previewBox.style.backgroundRepeat = 'no-repeat';
    }

    function fillQuestionData(wrapper, question) {
        wrapper.dataset.questionId = question.id || '';
        const type = question.type;

        if (type === 'mcq-text') {
            wrapper.querySelector('#mcqQuestion').value = question.question_text || '';
            const grid = wrapper.querySelector('#mcqChoicesGrid');
            const choices = [question.option_a, question.option_b, question.option_c, question.option_d].filter(v => (v || '').trim() !== '');
            const addBtn = wrapper.querySelector('#mcqAddChoice');
            while (grid.children.length < choices.length) addBtn.click();
            Array.from(grid.children).forEach((card, idx) => {
                const input = card.querySelector("input[type='text']");
                const radio = card.querySelector("input[type='radio']");
                input.value = choices[idx] || '';
                radio.checked = (choices[idx] || '').trim().toLowerCase() === (question.correct_answer || '').trim().toLowerCase();
            });
        } else if (type === 'audio-image') {
            wrapper.querySelector('#instruction').value = question.question_text || '';
            const labels = splitCsv(question.audioImage_label);
            const images = splitCsv(question.image_file);
            const grid = wrapper.querySelector('#choicesGrid');
            const addBtn = wrapper.querySelector('#addChoice');
            while (grid.children.length < labels.length) addBtn.click();
            Array.from(grid.children).forEach((card, idx) => {
                const input = card.querySelector("input[type='text']");
                const radio = card.querySelector("input[type='radio']");
                input.value = labels[idx] || '';
                radio.checked = (labels[idx] || '').trim().toLowerCase() === (question.correct_answer || '').trim().toLowerCase();
                if (images[idx]) setExistingImagePreview(card.querySelector('.h-32'), images[idx]);
            });
            if (question.audio_file) {
                const audioWrap = wrapper.querySelector('#audioUpload')?.parentElement;
                if (audioWrap) showExistingMediaNote(audioWrap, `Audio semasa: ${question.audio_file}`);
            }
        } else if (type === 'match-image') {
            wrapper.querySelector('#matchInstruction').value = question.question_text || '';
            const words = splitCsv(question.correct_answer);
            const images = splitCsv(question.image_file);
            const list = wrapper.querySelector('#matchPairList');
            const addBtn = wrapper.querySelector('#addPair');
            while (list.children.length < words.length) addBtn.click();
            Array.from(list.children).forEach((row, idx) => {
                const input = row.querySelector("input[type='text']");
                input.value = words[idx] || '';
                if (images[idx]) setExistingImagePreview(row.querySelector('.match-image-preview'), images[idx]);
            });
        } else if (type === 'true-false') {
            wrapper.querySelector('#tfQuestion').value = question.question_text || '';
            const correct = (question.correct_answer || '').toLowerCase();
            const radio = wrapper.querySelector(`input[type='radio'][value='${correct}']`);
            if (radio) radio.checked = true;
            if (question.image_file) {
                setExistingImagePreview(wrapper.querySelector('#tfImagePreview'), question.image_file);
            }
        } else if (type === 'drag-drop') {
            wrapper.querySelector('#instruction').value = question.question_text || '';
            const words = splitCsv(question.correct_answer);
            const wordInputs = wrapper.querySelector('#wordInputs');
            const addWordBtn = wrapper.querySelector('#addWordRow');
            while (wordInputs.querySelectorAll('.word-input').length < words.length) addWordBtn.click();
            Array.from(wordInputs.querySelectorAll('.word-input')).forEach((input, idx) => {
                input.value = words[idx] || '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }
    }

    function collectQuestions(formData) {
        const wrappers = Array.from(document.querySelectorAll("#formContainer .question-wrapper"));
        const questions = [];

        wrappers.forEach((wrapper, qIdx) => {
            const type = wrapper.dataset.questionType;
            const qData = { type };
            const questionId = Number(wrapper.dataset.questionId || 0);
            if (questionId > 0) qData.id = questionId;

            if (type === "mcq-text") {
                qData.question_text = wrapper.querySelector('#mcqQuestion')?.value?.trim() || "";
                const choiceRows = wrapper.querySelectorAll('#mcqChoicesGrid > div');
                qData.choices = Array.from(choiceRows).map(row => ({
                    text: row.querySelector("input[type='text']")?.value || "",
                    is_correct: row.querySelector("input[type='radio']")?.checked || false
                }));
            } else if (type === "audio-image") {
                qData.question_text = wrapper.querySelector("#instruction")?.value || "Dengar dan pilih.";
                const audioInput = wrapper.querySelector("input[type='file'][accept*='audio']");
                if (audioInput?.files[0]) {
                    const key = `q_${qIdx}_audio`;
                    formData.append(key, audioInput.files[0]);
                    qData.audio_key = key;
                }
                const choiceCards = wrapper.querySelectorAll('#choicesGrid > div')
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
            } else if (type === "match-image") {
                qData.question_text = wrapper.querySelector('#matchInstruction')?.value?.trim() || 'Padankan perkataan dengan gambar yang betul.';
                const pairRows = wrapper.querySelectorAll('#matchPairList > div');
                qData.pairs = Array.from(pairRows).map((row, pIdx) => {
                    const pair = { word: row.querySelector("input[type='text']")?.value?.trim() || "" };
                    const imgInput = row.querySelector("input[type='file']");
                    if (imgInput?.files[0]) {
                        const imgKey = `q_${qIdx}_p_${pIdx}_img`;
                        formData.append(imgKey, imgInput.files[0]);
                        pair.image_key = imgKey;
                    }
                    return pair;
                });
            } else if (type === "true-false") {
                qData.question_text = wrapper.querySelector('#tfQuestion')?.value?.trim() || '';
                qData.correct_answer = wrapper.querySelector("input[type='radio']:checked")?.value || null;
                const imgInput = wrapper.querySelector("input[type='file']");
                if (imgInput?.files?.[0]) {
                    const key = `q_${qIdx}_tf_image`;
                    formData.append(key, imgInput.files[0]);
                    qData.image_key = key;
                }
            } else if (type === "drag-drop") {
                const instruction = wrapper.querySelector('#instruction')?.value?.trim() || 'Susun perkataan ini menjadi ayat yang betul.';
                const words = Array.from(wrapper.querySelectorAll('.word-input')).map((input) => input.value.trim()).filter((word) => word.length > 0);
                qData.question_text = instruction;
                qData.words = words;
                qData.correct_answer = words.join(',');
            }

            questions.push(qData);
        });

        return questions;
    }

    document.getElementById("submitAllQuestions")?.addEventListener("click", async () => {
        const submitButton = document.getElementById("submitAllQuestions");
        const submitStatus = document.getElementById("submitStatus");

        const hwName = document.getElementById("homeworkName").value.trim();
        const classId = document.getElementById("classSelect").value;
        const dueDate = document.getElementById("dueDate").value;

        if (!hwName || !classId || !dueDate) {
            alert("Sila lengkapkan maklumat kerja rumah (Nama, Kelas, dan Tarikh).");
            return;
        }

        const formData = new FormData();
        const questions = collectQuestions(formData);

        if (questions.length === 0) {
            alert("Tambah sekurang-kurangnya satu soalan sebelum simpan.");
            return;
        }

        submitButton.disabled = true;
        submitStatus.textContent = "Sedang menyimpan...";

        formData.append("homework_id", homeworkId);
        formData.append("homework_name", hwName);
        formData.append("class_id", classId);
        formData.append("due_date", dueDate);
        formData.append("questions", JSON.stringify(questions));

        try {
            const response = await fetch("../backend/homework-updateBE.php", { method: "POST", body: formData });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || "Gagal simpan.");

            submitStatus.textContent = "Perubahan berjaya disimpan!";
            window.location.href = "work.php";
        } catch (error) {
            submitStatus.textContent = error.message;
            submitButton.disabled = false;
        }
    });

    (async function preloadExistingQuestions() {
        for (const question of existingQuestions) {
            const { wrapper } = await loadForm(question.type);
            fillQuestionData(wrapper, question);
        }
    })();
    </script>

    <script src="../js/drag-drop.js"></script>
    <script src="../js/mcq-text.js"></script>
    <script src="../js/audio-image.js"></script>
    <script src="../js/match-image.js"></script>
    <script src="../js/true-false.js"></script>
    <script src="../js/initQuestion.js"></script>
</body>
</html>
