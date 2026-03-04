<?php
session_start();
include('../config/config.php');

$homeworkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$isTeacher = isset($_SESSION['role']) && $_SESSION['role'] === 'Pensyarah';

$homework = null;
$questions = [];
$studentSubmissionRows = [];

if ($homeworkId > 0) {
    $homeworkStmt = mysqli_prepare($con, 'SELECT id, title, description, class, due_date FROM homework WHERE id = ? LIMIT 1');
    mysqli_stmt_bind_param($homeworkStmt, 'i', $homeworkId);
    mysqli_stmt_execute($homeworkStmt);
    $homeworkResult = mysqli_stmt_get_result($homeworkStmt);
    $homework = mysqli_fetch_assoc($homeworkResult);

    $questionStmt = mysqli_prepare(
        $con,
        'SELECT id, type, question_text, option_a, option_b, option_c, option_d, audioImage_label, audio_file, image_file, correct_answer
         FROM questions
         WHERE homework_id = ?
         ORDER BY id ASC'
    );
    mysqli_stmt_bind_param($questionStmt, 'i', $homeworkId);
    mysqli_stmt_execute($questionStmt);
    $questionResult = mysqli_stmt_get_result($questionStmt);

    while ($row = mysqli_fetch_assoc($questionResult)) {
        $questions[] = $row;
    }

    if ($isTeacher) {
        $studentsStmt = mysqli_prepare(
            $con,
            "SELECT u.id, u.nama,
                    shs.status,
                    shs.score,
                    shs.incorrect
             FROM users u
             LEFT JOIN student_homework_submissions shs
                ON shs.student_id = u.id AND shs.homework_id = ?
             WHERE u.role = 'Pelajar'
             ORDER BY u.nama ASC"
        );
        mysqli_stmt_bind_param($studentsStmt, 'i', $homeworkId);
        mysqli_stmt_execute($studentsStmt);
        $studentsResult = mysqli_stmt_get_result($studentsStmt);

        while ($studentRow = mysqli_fetch_assoc($studentsResult)) {
            $studentSubmissionRows[] = $studentRow;
        }
    }
}

function normalizePath(?string $path): string {
    if (!$path) return '';

    $normalized = str_replace('\\', '/', trim($path));
    if ($normalized === '') return '';

    if (preg_match('#^(https?:)?//#i', $normalized) || preg_match('#^(data|blob):#i', $normalized)) {
        return $normalized;
    }

    $mediaPos = stripos($normalized, 'media/');
    if ($mediaPos !== false) {
        $normalized = substr($normalized, $mediaPos);
    }

    return '../' . ltrim($normalized, './');
}

function splitCsv(?string $value): array {
    if (!$value) return [];
    $items = array_map('trim', explode(',', $value));
    return array_values(array_filter($items, fn($item) => $item !== ''));
}

function splitCsvKeepingIndex(?string $value): array {
    if ($value === null) return [];
    return array_map('trim', explode(',', $value));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $isTeacher ? 'Lihat Kerja Rumah' : 'Jawab Kerja Rumah'; ?></title>
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('navbar.php'); ?>

<div class="max-w-5xl mx-auto px-6 py-8 space-y-6">
    <?php if (!$homework): ?>
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center space-y-3">
            <h1 class="text-xl font-semibold text-gray-800">Kerja rumah tidak ditemui</h1>
            <a href="work.php" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white hover:bg-red-700">Kembali</a>
        </section>
    <?php else: ?>
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800"><?php echo htmlspecialchars($homework['title']); ?></h1>
            <p class="text-sm text-gray-500"><?php echo htmlspecialchars($homework['description'] ?: 'Jawab semua soalan di bawah.'); ?></p>
            <div class="flex flex-wrap gap-2 text-xs text-gray-600">
                <span class="px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200">Kelas: <?php echo htmlspecialchars($homework['class']); ?></span>
                <span class="px-2.5 py-1 rounded-full bg-gray-100 border border-gray-200">Tarikh akhir: <?php echo htmlspecialchars($homework['due_date']); ?></span>
            </div>
        </section>

        <div id="questionList" class="space-y-4">
            <?php foreach ($questions as $index => $question): ?>
                <?php
                    $type = $question['type'];
                    $qId = (int)$question['id'];
                    $labels = splitCsv($question['audioImage_label'] ?? '');
                    $imagePaths = array_map('normalizePath', splitCsvKeepingIndex($question['image_file'] ?? null));
                ?>
                <article class="question-card bg-white rounded-2xl border border-gray-200 shadow-sm p-6 space-y-4" data-question-id="<?php echo $qId; ?>" data-question-type="<?php echo htmlspecialchars($type); ?>">
                    <header class="space-y-1">
                        <p class="text-xs uppercase tracking-wide font-bold text-gray-500">Soalan <?php echo $index + 1; ?> · <?php echo htmlspecialchars($type); ?></p>
                        <h2 class="text-base font-semibold text-gray-800"><?php echo htmlspecialchars($question['question_text']); ?></h2>
                    </header>

                    <?php if ($type === 'mcq' || $type === 'truefalse' || $type === 'listening'): ?>
                        <?php if ($type === 'listening' && !empty($question['audio_file'])): ?>
                            <audio controls class="w-full" preload="metadata" src="<?php echo htmlspecialchars(normalizePath($question['audio_file'])); ?>"></audio>
                        <?php endif; ?>
                        <?php if ($type === 'truefalse' && !empty($question['image_file'])): ?>
                            <img src="<?php echo htmlspecialchars(normalizePath($question['image_file'])); ?>" alt="Soalan true/false" class="w-full max-h-64 object-cover rounded-lg border border-gray-200">
                        <?php endif; ?>

                        <div class="grid gap-2">
                            <?php if ($type === 'truefalse'): ?>
                                <?php foreach (['true' => 'Betul', 'false' => 'Salah'] as $value => $text): ?>
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 <?php echo $isTeacher ? 'cursor-default' : 'cursor-pointer'; ?>">
                                        <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($value); ?>" class="text-red-600 focus:ring-red-500" <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($text); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php elseif ($type === 'listening' && !empty($labels)): ?>
                                <?php foreach ($labels as $labelIndex => $label): ?>
                                    <?php $choiceImage = $imagePaths[$labelIndex] ?? ''; ?>
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 <?php echo $isTeacher ? 'cursor-default' : 'cursor-pointer'; ?>">
                                        <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($label); ?>" class="text-red-600 focus:ring-red-500" <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                        <?php if ($choiceImage !== ''): ?>
                                            <img src="<?php echo htmlspecialchars($choiceImage); ?>" alt="Pilihan audio gambar" class="h-14 w-14 rounded-md object-cover border border-gray-200">
                                        <?php endif; ?>
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($label); ?></span>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <?php foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $optionKey): ?>
                                    <?php if (!empty($question[$optionKey])): ?>
                                        <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 <?php echo $isTeacher ? 'cursor-default' : 'cursor-pointer'; ?>">
                                            <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($question[$optionKey]); ?>" class="text-red-600 focus:ring-red-500" <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($question[$optionKey]); ?></span>
                                        </label>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>
                    <?php elseif ($type === 'picture'): ?>
                        <?php $matchLabels = splitCsv($question['correct_answer'] ?? ''); ?>
                        <div class="space-y-3">
                            <div class="grid gap-3 sm:grid-cols-2">
                                <?php foreach ($imagePaths as $imageIndex => $imagePath): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 space-y-2">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Gambar padanan" class="h-36 w-full object-cover rounded-md">
                                        <input type="text" class="match-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Jawapan untuk gambar ini" data-match-index="<?php echo $imageIndex; ?>" data-word-hint="<?php echo htmlspecialchars($matchLabels[$imageIndex] ?? ''); ?>" <?php echo $isTeacher ? 'disabled' : ''; ?>>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($type === 'rearrange'): ?>
                        <?php $tokens = splitCsv($question['correct_answer'] ?? ''); shuffle($tokens); ?>
                        <div class="space-y-3">
                            <div class="rearrange-bank flex flex-wrap gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <?php foreach ($tokens as $token): ?>
                                    <button type="button" draggable="<?php echo $isTeacher ? 'false' : 'true'; ?>" class="rearrange-token px-3 py-1.5 rounded-full bg-white border border-gray-300 text-sm text-gray-700 hover:bg-gray-100" data-token="<?php echo htmlspecialchars($token); ?>"><?php echo htmlspecialchars($token); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="rearrange-dropzone min-h-14 rounded-lg border-2 border-dashed border-gray-300 bg-white px-3 py-2 flex flex-wrap gap-2"></div>
                            <textarea rows="2" class="rearrange-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" readonly></textarea>
                        </div>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <?php if ($isTeacher): ?>
            <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <h2 class="text-lg font-semibold text-gray-800">Senarai Pelajar</h2>
                <div class="space-y-2">
                    <?php foreach ($studentSubmissionRows as $student): ?>
                        <?php
                            $submitted = ($student['status'] ?? '') === 'submitted';
                            $score = is_numeric($student['score']) ? (int)$student['score'] : 0;
                            $incorrect = is_numeric($student['incorrect']) ? (int)$student['incorrect'] : 0;
                            $totalQuestions = max(count($questions), $score + $incorrect);
                        ?>
                        <div class="flex items-center justify-between rounded-lg border border-gray-200 px-4 py-3">
                            <div>
                                <p class="font-medium text-gray-800"><?php echo htmlspecialchars($student['nama']); ?></p>
                                <p class="text-xs text-gray-500">Skor: <?php echo $score; ?>/<?php echo $totalQuestions; ?></p>
                            </div>
                            <span class="px-3 py-1 text-xs rounded-full <?php echo $submitted ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'; ?>">
                                <?php echo $submitted ? 'Submitted' : 'Belum Submit'; ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php else: ?>
            <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
                <button id="submitStudentAnswers" type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-base font-semibold text-white hover:bg-red-700 disabled:opacity-60">Hantar Jawapan</button>
                <p id="answerStatus" class="text-center text-sm text-gray-600"></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php if (!$isTeacher): ?>
<script>
(function () {
    const list = document.getElementById('questionList');
    if (!list) return;

    const updateRearrangeAnswer = (card) => {
        const textarea = card.querySelector('.rearrange-answer');
        const dropzone = card.querySelector('.rearrange-dropzone');
        if (!textarea || !dropzone) return;
        const ordered = Array.from(dropzone.querySelectorAll('.rearrange-token')).map((token) => (token.dataset.token || token.textContent || '').trim()).filter(Boolean);
        textarea.value = ordered.join(' ');
    };

    list.addEventListener('dragstart', (event) => {
        const tokenButton = event.target.closest('.rearrange-token');
        if (!tokenButton) return;
        const card = tokenButton.closest('.question-card');
        if (!card) return;
        tokenButton.classList.add('opacity-60');
        event.dataTransfer.setData('text/plain', tokenButton.dataset.token || tokenButton.textContent || '');
        event.dataTransfer.setData('source-card-id', card.dataset.questionId || '');
    });

    list.addEventListener('dragend', (event) => {
        const tokenButton = event.target.closest('.rearrange-token');
        if (tokenButton) tokenButton.classList.remove('opacity-60');
    });

    list.addEventListener('dragover', (event) => {
        const dropzone = event.target.closest('.rearrange-dropzone, .rearrange-bank');
        if (!dropzone) return;
        event.preventDefault();
    });

    list.addEventListener('drop', (event) => {
        const targetZone = event.target.closest('.rearrange-dropzone, .rearrange-bank');
        if (!targetZone) return;
        event.preventDefault();

        const card = targetZone.closest('.question-card');
        if (!card) return;
        const sourceCardId = event.dataTransfer.getData('source-card-id');
        if (sourceCardId !== (card.dataset.questionId || '')) return;

        const draggingToken = card.querySelector('.rearrange-token.opacity-60');
        if (!draggingToken) return;

        targetZone.appendChild(draggingToken);
        updateRearrangeAnswer(card);
    });

    const submitButton = document.getElementById('submitStudentAnswers');
    const answerStatus = document.getElementById('answerStatus');

    submitButton?.addEventListener('click', async () => {
        const cards = Array.from(document.querySelectorAll('.question-card'));
        const answers = cards.map((card) => {
            const questionId = Number(card.dataset.questionId || 0);
            const type = card.dataset.questionType || '';
            let answerValue = '';

            if (type === 'picture') {
                answerValue = Array.from(card.querySelectorAll('.match-answer')).map((input) => input.value.trim()).filter(Boolean).join(',');
            } else if (type === 'rearrange') {
                answerValue = card.querySelector('.rearrange-answer')?.value?.trim() || '';
            } else {
                const selected = card.querySelector('input[type="radio"]:checked');
                answerValue = selected ? selected.value : '';
            }

            return { question_id: questionId, type, answer_text: answerValue };
        });

        if (answers.some((item) => item.answer_text === '')) {
            answerStatus.textContent = 'Sila jawab semua soalan sebelum hantar.';
            answerStatus.className = 'text-center text-sm text-red-600';
            return;
        }

        const payload = {
            homework_id: <?php echo (int)$homeworkId; ?>,
            student_id: <?php echo isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; ?>,
            answers,
        };

        submitButton.disabled = true;
        answerStatus.textContent = 'Sedang menghantar jawapan...';
        answerStatus.className = 'text-center text-sm text-gray-600';

        try {
            const response = await fetch('../backend/student-homework-answerBE.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || 'Gagal menghantar jawapan.');
            answerStatus.textContent = `Jawapan berjaya dihantar! Skor: ${result.score}/${result.score + result.incorrect}`;
            answerStatus.className = 'text-center text-sm text-green-600';
        } catch (error) {
            answerStatus.textContent = error.message;
            answerStatus.className = 'text-center text-sm text-red-600';
            submitButton.disabled = false;
        }
    });
})();
</script>
<?php endif; ?>
</body>
</html>
