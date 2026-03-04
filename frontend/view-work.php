<?php
session_start();
include('../config/config.php');

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Pelajar') {
    header('Location: work.php');
    exit;
}

$homeworkId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$homework = null;
$questions = [];

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
}

function normalizePath(?string $path): string {
    if (!$path) return '';

    $normalized = trim($path);
    if ($normalized === '') return '';

    // 1. Fix Windows backslashes
    $normalized = str_replace('\\', '/', $normalized);

    // 2. Ignore remote URLs
    if (preg_match('#^(https?:)?//#i', $normalized) || preg_match('#^(data|blob):#i', $normalized)) {
        return $normalized;
    }

    // 3. Find the 'media/' folder position
    // This handles cases where the DB stores 'C:/xampp/htdocs/mandrin-project/media/image.jpg'
    $mediaPos = stripos($normalized, 'media/');
    if ($mediaPos !== false) {
        $normalized = substr($normalized, $mediaPos);
    }

    // 4. Clean up leading slashes/dots
    $normalized = ltrim($normalized, './');

    // 5. Assuming your images are in mandrin-project/media/
    // and this file is in mandrin-project/frontend/
    // We go up one level to the project root, then into media
    return '../' . $normalized;
}

function splitCsv(?string $value): array
{
    if (!$value) {
        return [];
    }

    $items = array_map('trim', explode(',', $value));
    return array_values(array_filter($items, fn($item) => $item !== ''));
}


function splitCsvKeepingIndex(?string $value): array
{
    if ($value === null) {
        return [];
    }

    return array_map('trim', explode(',', $value));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jawab Kerja Rumah</title>
    <?php include('header.php'); ?>
</head>
<body class="bg-gray-100 min-h-screen">
<?php include('navbar.php'); ?>

<div class="max-w-5xl mx-auto px-6 py-8 space-y-6">
    <?php if (!$homework): ?>
        <section class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8 text-center space-y-3">
            <h1 class="text-xl font-semibold text-gray-800">Kerja rumah tidak ditemui</h1>
            <p class="text-sm text-gray-500">Sila kembali ke senarai kerja rumah dan pilih tugasan yang sah.</p>
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

                    <?php if ($type === 'mcq'): ?>
                        <div class="grid gap-2">
                            <?php foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $optionKey): ?>
                                <?php if (!empty($question[$optionKey])): ?>
                                    <label class="flex items-center gap-3 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                        <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($question[$optionKey]); ?>" class="text-red-600 focus:ring-red-500">
                                        <span class="text-sm text-gray-700"><?php echo htmlspecialchars($question[$optionKey]); ?></span>
                                    </label>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif ($type === 'truefalse'): ?>
                        <div class="space-y-3">
                            <?php if (!empty($question['image_file'])): ?>
                                <img src="<?php echo htmlspecialchars(normalizePath($question['image_file'])); ?>" alt="Soalan true/false" class="w-full max-h-64 object-cover rounded-lg border border-gray-200">
                            <?php endif; ?>
                            <div class="flex gap-3">
                                <label class="flex-1 flex items-center gap-2 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="answer_<?php echo $qId; ?>" value="true" class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700">Betul</span>
                                </label>
                                <label class="flex-1 flex items-center gap-2 p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                                    <input type="radio" name="answer_<?php echo $qId; ?>" value="false" class="text-red-600 focus:ring-red-500">
                                    <span class="text-sm font-medium text-gray-700">Salah</span>
                                </label>
                            </div>
                        </div>
                    <?php elseif ($type === 'listening'): ?>
                        <div class="space-y-4">
                            <?php if (!empty($question['audio_file'])): ?>
                               <audio controls class="w-full" preload="metadata" src="<?php echo htmlspecialchars(normalizePath($question['audio_file'])); ?>">
                                    Browser anda tidak menyokong audio.
                                </audio>
                            <?php endif; ?>

                            <div class="grid gap-3 sm:grid-cols-2">
                                <?php if (!empty($labels)): ?>
                                    <?php foreach ($labels as $choiceIndex => $label): ?>
                                        <label class="block border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer">
                                            <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($label); ?>" class="mr-2 text-red-600 focus:ring-red-500">
                                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($label); ?></span>
                                            <?php if (!empty($imagePaths[$choiceIndex])): ?>
                                                <img src="<?php echo htmlspecialchars($imagePaths[$choiceIndex]); ?>" alt="Pilihan gambar" class="mt-2 h-32 w-full object-cover rounded-md border border-gray-200">
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <?php foreach (['option_a', 'option_b', 'option_c', 'option_d'] as $optionKey): ?>
                                        <?php if (!empty($question[$optionKey])): ?>
                                            <label class="block border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer">
                                                <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($question[$optionKey]); ?>" class="mr-2 text-red-600 focus:ring-red-500">
                                                <span class="text-sm text-gray-700"><?php echo htmlspecialchars($question[$optionKey]); ?></span>
                                            </label>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($type === 'picture'): ?>
                        <?php
                            $matchLabels = splitCsv($question['audioImage_label'] ?? '');
                            if (empty($matchLabels)) {
                                $matchLabels = splitCsv($question['correct_answer'] ?? '');
                            }
                        ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Padankan gambar dengan perkataan yang sesuai.</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <?php foreach ($imagePaths as $imageIndex => $imagePath): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 space-y-2">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Gambar padanan" class="h-36 w-full object-cover rounded-md">
                                        <input type="text" class="match-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Jawapan untuk gambar ini" data-match-index="<?php echo $imageIndex; ?>" data-word-hint="<?php echo htmlspecialchars($matchLabels[$imageIndex] ?? ''); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($type === 'rearrange'): ?>
                        <?php $tokens = splitCsv($question['correct_answer'] ?? ''); shuffle($tokens); ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Seret perkataan ke ruang jawapan untuk susun ayat yang betul.</p>
                            <div class="rearrange-bank flex flex-wrap gap-2 rounded-lg border border-gray-200 bg-gray-50 p-3">
                                <?php foreach ($tokens as $token): ?>
                                    <button type="button" draggable="true" class="rearrange-token px-3 py-1.5 rounded-full bg-white border border-gray-300 text-sm text-gray-700 hover:bg-gray-100" data-token="<?php echo htmlspecialchars($token); ?>"><?php echo htmlspecialchars($token); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="rearrange-dropzone min-h-14 rounded-lg border-2 border-dashed border-gray-300 bg-white px-3 py-2 flex flex-wrap gap-2" aria-label="Drop words here"></div>
                            <div class="flex items-center justify-between gap-3">
                                <p class="text-xs text-gray-500">Tip: anda boleh seret semula token ke bank untuk buang susunan.</p>
                                <button type="button" class="rearrange-reset text-xs font-semibold text-red-600 hover:text-red-700">Reset</button>
                            </div>
                            <textarea rows="2" class="rearrange-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Jawapan akan diisi automatik..." readonly></textarea>
                        </div>
                    <?php else: ?>
                        <textarea rows="3" class="generic-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Tulis jawapan anda di sini..."></textarea>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>

        <div class="bg-white rounded-2xl border border-gray-200 shadow-sm p-5 space-y-3">
            <button id="submitStudentAnswers" type="button" class="w-full inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-base font-semibold text-white hover:bg-red-700 disabled:opacity-60">Hantar Jawapan</button>
            <p id="answerStatus" class="text-center text-sm text-gray-600"></p>
        </div>
    <?php endif; ?>
</div>

<script>
(function () {
    const list = document.getElementById('questionList');
    if (!list) return;

    const updateRearrangeAnswer = (card) => {
        const textarea = card.querySelector('.rearrange-answer');
        const dropzone = card.querySelector('.rearrange-dropzone');
        if (!textarea || !dropzone) return;

        const ordered = Array.from(dropzone.querySelectorAll('.rearrange-token'))
            .map((token) => token.dataset.token || token.textContent || '')
            .map((value) => value.trim())
            .filter(Boolean);

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
        event.dataTransfer.effectAllowed = 'move';
        card.dataset.draggingToken = tokenButton.dataset.token || tokenButton.textContent || '';
    });

    list.addEventListener('dragend', (event) => {
        const tokenButton = event.target.closest('.rearrange-token');
        if (tokenButton) tokenButton.classList.remove('opacity-60');
    });

    list.addEventListener('dragover', (event) => {
        const dropzone = event.target.closest('.rearrange-dropzone, .rearrange-bank');
        if (!dropzone) return;
        event.preventDefault();
        dropzone.classList.add('border-red-400');
    });

    list.addEventListener('dragleave', (event) => {
        const dropzone = event.target.closest('.rearrange-dropzone, .rearrange-bank');
        if (!dropzone) return;
        dropzone.classList.remove('border-red-400');
    });

    list.addEventListener('drop', (event) => {
        const targetZone = event.target.closest('.rearrange-dropzone, .rearrange-bank');
        if (!targetZone) return;
        event.preventDefault();
        targetZone.classList.remove('border-red-400');

        const card = targetZone.closest('.question-card');
        if (!card) return;

        const sourceCardId = event.dataTransfer.getData('source-card-id');
        const tokenText = event.dataTransfer.getData('text/plain').trim();
        if (!tokenText || sourceCardId !== (card.dataset.questionId || '')) return;

        const draggingToken = card.querySelector('.rearrange-token.opacity-60');
        if (!draggingToken) return;

        targetZone.appendChild(draggingToken);
        updateRearrangeAnswer(card);
    });

    list.addEventListener('click', (event) => {
        const resetBtn = event.target.closest('.rearrange-reset');
        if (!resetBtn) return;

        const card = resetBtn.closest('.question-card');
        if (!card) return;

        const bank = card.querySelector('.rearrange-bank');
        const dropzone = card.querySelector('.rearrange-dropzone');
        if (!bank || !dropzone) return;

        Array.from(dropzone.querySelectorAll('.rearrange-token')).forEach((token) => bank.appendChild(token));
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
                const entries = Array.from(card.querySelectorAll('.match-answer')).map((input) => input.value.trim()).filter(Boolean);
                answerValue = entries.join(',');
            } else if (type === 'rearrange') {
                answerValue = card.querySelector('.rearrange-answer')?.value?.trim() || '';
            } else {
                const selected = card.querySelector('input[type="radio"]:checked');
                answerValue = selected ? selected.value : (card.querySelector('.generic-answer')?.value?.trim() || '');
            }

            return {
                question_id: questionId,
                type,
                answer_text: answerValue,
            };
        });

        const unanswered = answers.some((item) => item.answer_text === '');
        if (unanswered) {
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
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(payload),
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Gagal menghantar jawapan.');
            }

            answerStatus.textContent = 'Jawapan berjaya dihantar!';
            answerStatus.className = 'text-center text-sm text-green-600';
        } catch (error) {
            answerStatus.textContent = error.message;
            answerStatus.className = 'text-center text-sm text-red-600';
            submitButton.disabled = false;
        }
    });
})();
</script>
</body>
</html>