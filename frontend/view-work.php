<?php
session_start();
include('../config/config.php');

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

function normalizePath(?string $path): string
{
    if (!$path) {
        return '';
    }

    $normalized = preg_replace('#^\.\./#', '../', $path);
    return $normalized ?? '';
}

function splitCsv(?string $value): array
{
    if (!$value) {
        return [];
    }

    $items = array_map('trim', explode(',', $value));
    return array_values(array_filter($items, fn($item) => $item !== ''));
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
                    $imagePaths = array_map('normalizePath', splitCsv($question['image_file'] ?? ''));
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
                                <audio controls class="w-full">
                                    <source src="<?php echo htmlspecialchars(normalizePath($question['audio_file'])); ?>" type="audio/mpeg">
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
                        <?php $words = splitCsv($question['correct_answer'] ?? ''); ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Padankan gambar dengan perkataan yang sesuai.</p>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <?php foreach ($imagePaths as $imageIndex => $imagePath): ?>
                                    <div class="border border-gray-200 rounded-lg p-3 space-y-2">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Gambar padanan" class="h-36 w-full object-cover rounded-md">
                                        <input type="text" class="match-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Jawapan untuk gambar ini" data-match-index="<?php echo $imageIndex; ?>" data-word-hint="<?php echo htmlspecialchars($words[$imageIndex] ?? ''); ?>">
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php elseif ($type === 'rearrange'): ?>
                        <?php $tokens = splitCsv($question['correct_answer'] ?? ''); shuffle($tokens); ?>
                        <div class="space-y-3">
                            <p class="text-sm text-gray-600">Susun perkataan ini menjadi ayat yang betul.</p>
                            <div class="flex flex-wrap gap-2">
                                <?php foreach ($tokens as $token): ?>
                                    <button type="button" class="rearrange-token px-3 py-1.5 rounded-full bg-gray-100 border border-gray-200 text-sm text-gray-700 hover:bg-gray-200" data-token="<?php echo htmlspecialchars($token); ?>"><?php echo htmlspecialchars($token); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <textarea rows="2" class="rearrange-answer w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm" placeholder="Bina ayat anda di sini..."></textarea>
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

    list.addEventListener('click', (event) => {
        const tokenButton = event.target.closest('.rearrange-token');
        if (!tokenButton) return;

        const card = tokenButton.closest('.question-card');
        const textarea = card?.querySelector('.rearrange-answer');
        if (!textarea) return;

        const token = tokenButton.dataset.token || '';
        textarea.value = textarea.value.trim() ? `${textarea.value.trim()} ${token}` : token;
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