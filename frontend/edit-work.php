<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pensyarah') {
    header('Location: work.php');
    exit;
}

$homeworkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($homeworkId <= 0) {
    header('Location: work.php');
    exit;
}

$homeworkStmt = mysqli_prepare($con, 'SELECT id, title, class, due_date FROM homework WHERE id = ? LIMIT 1');
mysqli_stmt_bind_param($homeworkStmt, 'i', $homeworkId);
mysqli_stmt_execute($homeworkStmt);
$homeworkResult = mysqli_stmt_get_result($homeworkStmt);
$homework = mysqli_fetch_assoc($homeworkResult);

if (!$homework) {
    header('Location: work.php');
    exit;
}

$questionStmt = mysqli_prepare($con, 'SELECT id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, correct_answer, audio_file, image_file FROM questions WHERE homework_id = ? ORDER BY id ASC');
mysqli_stmt_bind_param($questionStmt, 'i', $homeworkId);
mysqli_stmt_execute($questionStmt);
$questionResult = mysqli_stmt_get_result($questionStmt);

$existingQuestions = [];
while ($q = mysqli_fetch_assoc($questionResult)) {
    $existingQuestions[] = $q;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Work</title>
    <link rel="stylesheet" href="../css/work.php">
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-100 min-h-screen">
    <?php include('navbar.php'); ?>

    <div class="max-w-5xl mx-auto px-6 py-8 space-y-10">
        <section class="bg-white shadow-md rounded-2xl border border-gray-200 p-8 space-y-6">
            <header class="space-y-2">
                <h1 class="text-2xl font-semibold text-gray-800">Edit Kerja Rumah</h1>
                <p class="text-gray-500">Kemaskini maklumat dan soalan kerja rumah.</p>
            </header>

            <div class="space-y-4">
                <div>
                    <label for="homeworkName" class="block text-sm font-medium text-gray-700 mb-1">Nama Kerja Rumah</label>
                    <input id="homeworkName" type="text" value="<?php echo htmlspecialchars($homework['title']); ?>"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="classSelect" class="block text-sm font-medium text-gray-700 mb-1">Pilih Kelas</label>
                    <select id="classSelect" class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach (['1A', '1B', '2A', '2B'] as $cls): ?>
                            <option value="<?php echo $cls; ?>" <?php echo $homework['class'] === $cls ? 'selected' : ''; ?>>Kelas <?php echo $cls; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label for="dueDate" class="block text-sm font-medium text-gray-700 mb-1">Tarikh Akhir</label>
                    <input id="dueDate" type="date" value="<?php echo htmlspecialchars($homework['due_date']); ?>"
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>
            </div>
        </section>

        <div id="formContainer" class="space-y-6"></div>

        <section class="flex flex-col items-center space-y-4">
            <p class="text-lg font-medium text-gray-700">Tambah soalan baharu jika diperlukan</p>
            <div class="w-full bg-white shadow-md rounded-2xl border border-gray-200 flex justify-center gap-6 p-6">
                <button onclick="loadForm('drag-drop')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/drag.png" alt="Drag Drop" class="w-full object-contain"></button>
                <button onclick="loadForm('audio-image')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/hear.png" alt="Audio Image" class="w-full object-contain"></button>
                <button onclick="loadForm('match-image')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/match.png" alt="Match" class="w-full object-contain"></button>
                <button onclick="loadForm('mcq-text')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/mcq.png" alt="MCQ" class="w-full object-contain"></button>
                <button onclick="loadForm('true-false')" class="w-24 hover:scale-110 transition duration-200"><img src="../media/graphic/true-false.png" alt="True False" class="w-full object-contain"></button>
            </div>
        </section>

        <div class="pt-4">
            <button id="submitAllQuestions" type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-4 text-base font-bold text-white hover:bg-red-700 shadow-lg transition disabled:opacity-60">Simpan Perubahan</button>
            <p id="submitStatus" class="mt-3 text-center text-sm text-gray-600"></p>
        </div>
    </div>

<script>
const existingQuestions = <?php echo json_encode($existingQuestions, JSON_UNESCAPED_UNICODE); ?>;
const homeworkId = <?php echo (int)$homeworkId; ?>;

const formTitles = {
    'drag-drop': 'Drag & Drop',
    'audio-image': 'Audio + Image',
    'match-image': 'Match Image',
    'mcq-text': 'MCQ Text',
    'true-false': 'True / False',
};

function splitCsv(value) {
    if (!value) return [];
    return String(value).split(',').map((v) => v.trim());
}

function mapDbTypeToFormType(type) {
    if (type === 'mcq') return 'mcq-text';
    if (type === 'listening') return 'audio-image';
    if (type === 'picture') return 'match-image';
    if (type === 'truefalse') return 'true-false';
    if (type === 'rearrange') return 'drag-drop';
    return 'mcq-text';
}

async function loadForm(type, data = null) {
    const response = await fetch('form-loader.php?type=' + type);
    const html = await response.text();

    const wrapper = document.createElement('section');
    wrapper.className = 'question-wrapper relative border border-gray-200 rounded-2xl bg-white shadow-sm overflow-hidden mb-6';
    wrapper.dataset.questionType = type;

    const actions = document.createElement('div');
    actions.className = 'flex items-center justify-between px-6 py-3 border-b border-gray-100 bg-gray-50';

    const formLabel = document.createElement('p');
    formLabel.className = 'text-sm font-bold text-gray-700 uppercase tracking-wide';
    formLabel.textContent = formTitles[type] ?? 'Question Form';

    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'rounded-full bg-red-50 w-8 h-8 flex items-center justify-center text-red-600 hover:bg-red-600 hover:text-white transition';
    removeButton.innerHTML = '&times;';
    removeButton.addEventListener('click', () => wrapper.remove());

    actions.append(formLabel, removeButton);

    const content = document.createElement('div');
    content.className = 'p-6';
    content.innerHTML = html;
    content.querySelectorAll('footer').forEach((footer) => footer.remove());

    wrapper.append(actions, content);
    document.getElementById('formContainer').appendChild(wrapper);

    initQuestion(type, content);
    if (data) fillQuestionData(wrapper, type, data);
}

function fillQuestionData(wrapper, type, data) {
    if (type === 'mcq-text') {
        wrapper.querySelector('#mcqQuestion').value = data.question_text || '';
        const options = [data.option_a, data.option_b, data.option_c, data.option_d].filter(Boolean);
        const grid = wrapper.querySelector('#mcqChoicesGrid');
        grid.innerHTML = '';

        const addBtn = wrapper.querySelector('#mcqAddChoice');
        options.forEach(() => addBtn.click());
        const cards = Array.from(grid.children);
        cards.forEach((card, idx) => {
            const input = card.querySelector("input[type='text']");
            const radio = card.querySelector("input[type='radio']");
            if (input) input.value = options[idx] || '';
            if (radio && (options[idx] || '') === data.correct_answer) radio.checked = true;
        });
    } else if (type === 'audio-image') {
        wrapper.querySelector('#instruction').value = data.question_text || '';
        const labels = splitCsv(data.audioImage_label);
        const images = splitCsv(data.image_file);
        const grid = wrapper.querySelector('#choicesGrid');
        grid.innerHTML = '';

        const addBtn = wrapper.querySelector('#addChoice');
        labels.forEach(() => addBtn.click());

        const cards = Array.from(grid.children);
        cards.forEach((card, idx) => {
            const labelInput = card.querySelector("input[type='text']");
            const radio = card.querySelector("input[type='radio']");
            if (labelInput) labelInput.value = labels[idx] || '';
            if (radio && (labels[idx] || '') === data.correct_answer) radio.checked = true;
        });

        wrapper.dataset.existingAudioFile = data.audio_file || '';
        wrapper.dataset.existingImagePaths = JSON.stringify(images);
    } else if (type === 'match-image') {
        wrapper.querySelector('#matchInstruction').value = data.question_text || '';
        const words = splitCsv(data.correct_answer);
        const images = splitCsv(data.image_file);
        const list = wrapper.querySelector('#matchPairList');
        list.innerHTML = '';

        const addBtn = wrapper.querySelector('#addMatchPair');
        words.forEach(() => addBtn.click());

        const rows = Array.from(list.children);
        rows.forEach((row, idx) => {
            const input = row.querySelector("input[type='text']");
            if (input) input.value = words[idx] || '';
        });

        wrapper.dataset.existingImagePaths = JSON.stringify(images);
    } else if (type === 'true-false') {
        wrapper.querySelector('#tfQuestion').value = data.question_text || '';
        const radio = wrapper.querySelector(`input[type='radio'][value='${data.correct_answer}']`);
        if (radio) radio.checked = true;
        wrapper.dataset.existingImageFile = data.image_file || '';
    } else if (type === 'drag-drop') {
        wrapper.querySelector('#instruction').value = data.question_text || '';
        const words = splitCsv(data.correct_answer);
        const wordInputs = wrapper.querySelector('#wordInputs');
        wordInputs.innerHTML = '';
        words.forEach((word) => {
            const row = document.createElement('div');
            row.className = 'flex items-center gap-2';
            row.innerHTML = `<input type="text" class="word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" value="${word.replace(/"/g, '&quot;')}"><button type="button" class="remove-word text-gray-400 hover:text-red-500">✕</button>`;
            wordInputs.appendChild(row);
        });
    }
}

function collectQuestions(formData) {
    const wrappers = Array.from(document.querySelectorAll('#formContainer .question-wrapper'));
    const questions = [];

    wrappers.forEach((wrapper, qIdx) => {
        const type = wrapper.dataset.questionType;
        const qData = { type };

        if (type === 'mcq-text') {
            qData.question_text = wrapper.querySelector('#mcqQuestion')?.value?.trim() || '';
            const choiceRows = wrapper.querySelectorAll('#mcqChoicesGrid > div');
            qData.choices = Array.from(choiceRows).map((row) => ({
                text: row.querySelector("input[type='text']")?.value || '',
                is_correct: row.querySelector("input[type='radio']")?.checked || false,
            }));
        } else if (type === 'audio-image') {
            qData.question_text = wrapper.querySelector('#instruction')?.value || 'Dengar dan pilih.';
            const audioInput = wrapper.querySelector("input[type='file'][accept*='audio']");
            if (audioInput?.files[0]) {
                const key = `q_${qIdx}_audio`;
                formData.append(key, audioInput.files[0]);
                qData.audio_key = key;
            }
            qData.existing_audio_file = wrapper.dataset.existingAudioFile || '';

            const choiceCards = wrapper.querySelectorAll('#choicesGrid > div');
            qData.choices = Array.from(choiceCards).map((card, cIdx) => {
                const choice = {
                    label: card.querySelector("input[type='text']")?.value || '',
                    is_correct: card.querySelector("input[type='radio']")?.checked || false,
                };
                const imgInput = card.querySelector("input[type='file']");
                if (imgInput?.files[0]) {
                    const imgKey = `q_${qIdx}_c_${cIdx}_img`;
                    formData.append(imgKey, imgInput.files[0]);
                    choice.image_key = imgKey;
                }
                return choice;
            });
            qData.existing_image_paths = JSON.parse(wrapper.dataset.existingImagePaths || '[]');
        } else if (type === 'match-image') {
            qData.question_text = wrapper.querySelector('#matchInstruction')?.value?.trim() || 'Padankan perkataan dengan gambar yang betul.';
            const pairRows = wrapper.querySelectorAll('#matchPairList > div');
            qData.pairs = Array.from(pairRows).map((row, pIdx) => {
                const pair = { word: row.querySelector("input[type='text']")?.value?.trim() || '' };
                const imgInput = row.querySelector("input[type='file']");
                if (imgInput?.files[0]) {
                    const imgKey = `q_${qIdx}_p_${pIdx}_img`;
                    formData.append(imgKey, imgInput.files[0]);
                    pair.image_key = imgKey;
                }
                return pair;
            });
            qData.existing_image_paths = JSON.parse(wrapper.dataset.existingImagePaths || '[]');
        } else if (type === 'true-false') {
            qData.question_text = wrapper.querySelector('#tfQuestion')?.value?.trim() || '';
            qData.correct_answer = wrapper.querySelector("input[type='radio']:checked")?.value || null;
            const imgInput = wrapper.querySelector("input[type='file']");
            if (imgInput?.files?.[0]) {
                const key = `q_${qIdx}_tf_image`;
                formData.append(key, imgInput.files[0]);
                qData.image_key = key;
            }
            qData.existing_image_file = wrapper.dataset.existingImageFile || '';
        } else if (type === 'drag-drop') {
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

(async function preloadQuestions() {
    for (const question of existingQuestions) {
        await loadForm(mapDbTypeToFormType(question.type), question);
    }
})();

document.getElementById('submitAllQuestions').addEventListener('click', async () => {
    const submitButton = document.getElementById('submitAllQuestions');
    const submitStatus = document.getElementById('submitStatus');

    const hwName = document.getElementById('homeworkName').value.trim();
    const classId = document.getElementById('classSelect').value;
    const dueDate = document.getElementById('dueDate').value;

    if (!hwName || !classId || !dueDate) {
        alert('Sila lengkapkan maklumat kerja rumah (Nama, Kelas, dan Tarikh).');
        return;
    }

    const formData = new FormData();
    const questions = collectQuestions(formData);
    if (questions.length === 0) {
        alert('Tambah sekurang-kurangnya satu soalan sebelum simpan.');
        return;
    }

    submitButton.disabled = true;
    submitStatus.textContent = 'Sedang menyimpan...';

    formData.append('action', 'update');
    formData.append('homework_id', homeworkId);
    formData.append('homework_name', hwName);
    formData.append('class_id', classId);
    formData.append('due_date', dueDate);
    formData.append('questions', JSON.stringify(questions));

    try {
        const response = await fetch('../backend/homework-manageBE.php', { method: 'POST', body: formData });
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Gagal simpan.');
        submitStatus.textContent = 'Perubahan berjaya disimpan.';
        window.location.href = 'work.php';
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
