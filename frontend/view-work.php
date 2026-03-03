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
    if (!$path) return '';
    $normalized = preg_replace('#^\.\./#', '../', trim($path));
    return $normalized ?? '';
}

function splitCsvKeepOrder(?string $value): array
{
    if ($value === null || $value === '') return [];
    return array_map('trim', explode(',', $value));
}

function splitCsvWithoutEmpty(?string $value): array
{
    return array_values(array_filter(splitCsvKeepOrder($value), fn($item) => $item !== ''));
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
                    $labels = splitCsvKeepOrder($question['audioImage_label'] ?? '');
                    $imagePaths = array_map('normalizePath', splitCsvKeepOrder($question['image_file'] ?? ''));
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
                                <?php if (!empty(array_filter($labels, fn($l) => $l !== ''))): ?>
                                    <?php foreach ($labels as $choiceIndex => $label): ?>
                                        <?php if ($label === '') continue; ?>
                                        <label class="block border border-gray-200 rounded-lg p-3 hover:bg-gray-50 cursor-pointer">
                                            <input type="radio" name="answer_<?php echo $qId; ?>" value="<?php echo htmlspecialchars($label); ?>" class="mr-2 text-red-600 focus:ring-red-500">
                                            <span class="text-sm text-gray-700"><?php echo htmlspecialchars($label); ?></span>
                                            <?php if (!empty($imagePaths[$choiceIndex])): ?>
                                                <img src="<?php echo htmlspecialchars($imagePaths[$choiceIndex]); ?>" alt="Pilihan gambar" class="mt-2 h-32 w-full object-cover rounded-md border border-gray-200">
                                            <?php endif; ?>
                                        </label>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($type === 'picture'): ?>
                        <?php
                            $wordsRaw = splitCsvKeepOrder($question['correct_answer'] ?? '');
                            $imagesRaw = array_map('normalizePath', splitCsvKeepOrder($question['image_file'] ?? ''));
                            $pairs = [];
                            $count = max(count($wordsRaw), count($imagesRaw));
                            for ($i = 0; $i < $count; $i++) {
                                $word = trim($wordsRaw[$i] ?? '');
                                $image = trim($imagesRaw[$i] ?? '');
                                if ($word === '' || $image === '') continue;
                                $pairs[] = ['id' => (string)$i, 'word' => $word, 'image' => $image];
                            }
                            $shuffledWords = $pairs;
                            $shuffledImages = $pairs;
                            shuffle($shuffledWords);
                            shuffle($shuffledImages);
                        ?>
                        <div class="space-y-3 picture-match-card" data-pairs='<?= htmlspecialchars(json_encode($pairs), ENT_QUOTES, "UTF-8") ?>'>
                            <p class="text-sm text-gray-600">Seret garis dari perkataan (kiri) ke gambar (kanan).</p>
                            <div class="match-board relative grid gap-4 md:grid-cols-2 bg-gray-50 border border-gray-200 rounded-xl p-4">
                                <svg class="match-lines absolute inset-0 w-full h-full pointer-events-none"></svg>

                                <div class="space-y-3">
                                    <?php foreach ($shuffledWords as $item): ?>
                                        <div class="match-word flex items-center justify-between bg-white border border-gray-200 rounded-lg px-3 py-2" data-pair-id="<?= htmlspecialchars($item['id']) ?>">
                                            <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($item['word']) ?></span>
                                            <button type="button" class="connector-point word-point w-4 h-4 rounded-full border-2 border-red-500 bg-white" data-pair-id="<?= htmlspecialchars($item['id']) ?>"></button>
                                        </div>
                                    <?php endforeach; ?>
                                </div>

                                <div class="space-y-3">
                                    <?php foreach ($shuffledImages as $item): ?>
                                        <div class="match-image-item flex items-center gap-3 bg-white border border-gray-200 rounded-lg p-2" data-pair-id="<?= htmlspecialchars($item['id']) ?>">
                                            <button type="button" class="connector-point image-point w-4 h-4 rounded-full border-2 border-blue-500 bg-white shrink-0" data-pair-id="<?= htmlspecialchars($item['id']) ?>"></button>
                                            <img src="<?= htmlspecialchars($item['image']) ?>" alt="Gambar padanan" class="h-20 w-full object-cover rounded-md">
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <input type="hidden" class="picture-answer" value="">
                        </div>
                    <?php elseif ($type === 'rearrange'): ?>
                        <?php $tokens = splitCsvWithoutEmpty($question['correct_answer'] ?? ''); shuffle($tokens); ?>
                        <div class="space-y-3 rearrange-card">
                            <p class="text-sm text-gray-600">Seret dan lepaskan perkataan untuk membentuk ayat yang betul.</p>
                            <div class="rearrange-source flex flex-wrap gap-2 p-3 border border-dashed border-gray-300 rounded-lg bg-gray-50">
                                <?php foreach ($tokens as $token): ?>
                                    <button type="button" draggable="true" class="rearrange-token px-3 py-1.5 rounded-full bg-white border border-gray-300 text-sm text-gray-700 cursor-move" data-token="<?php echo htmlspecialchars($token); ?>"><?php echo htmlspecialchars($token); ?></button>
                                <?php endforeach; ?>
                            </div>
                            <div class="rearrange-drop flex flex-wrap gap-2 p-3 border border-gray-300 rounded-lg bg-white min-h-14">
                                <?php foreach ($tokens as $slotIndex => $token): ?>
                                    <div class="drop-slot min-w-[90px] h-10 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-xs text-gray-400" data-slot-index="<?= $slotIndex; ?>">Lepas sini</div>
                                <?php endforeach; ?>
                            </div>
                            <input type="hidden" class="rearrange-answer" value="">
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

    const setInvalid = (el) => {
        if (!el) return;
        el.classList.add('border-red-500', 'ring-1', 'ring-red-300');
    };

    const clearInvalid = (el) => {
        if (!el) return;
        el.classList.remove('border-red-500', 'ring-1', 'ring-red-300');
    };

    const updateRearrangeAnswer = (card) => {
        const answerInput = card.querySelector('.rearrange-answer');
        const words = Array.from(card.querySelectorAll('.drop-slot')).map((slot) => slot.dataset.word || '').filter(Boolean);
        answerInput.value = words.join(' ');
    };

    const makeSlotFilled = (slot, text) => {
        slot.dataset.word = text;
        slot.textContent = text;
        slot.classList.remove('text-gray-400', 'border-dashed');
        slot.classList.add('text-gray-700', 'border-solid');
    };

    const clearSlot = (slot) => {
        delete slot.dataset.word;
        slot.textContent = 'Lepas sini';
        slot.classList.add('text-gray-400', 'border-dashed');
        slot.classList.remove('text-gray-700', 'border-solid');
    };

    list.querySelectorAll('.rearrange-card').forEach((card) => {
        const source = card.querySelector('.rearrange-source');
        const tokens = source.querySelectorAll('.rearrange-token');
        const slots = card.querySelectorAll('.drop-slot');

        tokens.forEach((token) => {
            token.addEventListener('dragstart', (event) => {
                event.dataTransfer.setData('text/plain', token.dataset.token || '');
                event.dataTransfer.effectAllowed = 'move';
            });
        });

        slots.forEach((slot) => {
            slot.addEventListener('dragover', (event) => event.preventDefault());
            slot.addEventListener('drop', (event) => {
                event.preventDefault();
                const word = event.dataTransfer.getData('text/plain');
                if (!word) return;
                makeSlotFilled(slot, word);
                updateRearrangeAnswer(card);
            });

            slot.addEventListener('click', () => {
                if (slot.dataset.word) {
                    clearSlot(slot);
                    updateRearrangeAnswer(card);
                }
            });
        });
    });

    const getCenter = (container, element) => {
        const a = container.getBoundingClientRect();
        const b = element.getBoundingClientRect();
        return {
            x: b.left - a.left + b.width / 2,
            y: b.top - a.top + b.height / 2,
        };
    };

    const initializePictureCard = (card) => {
        const board = card.querySelector('.match-board');
        const svg = card.querySelector('.match-lines');
        const answerInput = card.querySelector('.picture-answer');
        const pairs = JSON.parse(card.dataset.pairs || '[]');
        const wordPoints = Array.from(card.querySelectorAll('.word-point'));
        const imagePoints = Array.from(card.querySelectorAll('.image-point'));

        const connections = new Map();
        let dragState = null;

        const refreshAnswer = () => {
            const answer = pairs.map((pair) => {
                const target = connections.get(pair.id) || '';
                return target;
            }).join(',');
            answerInput.value = answer;
        };

        const draw = () => {
            svg.innerHTML = '';
            connections.forEach((imageId, wordId) => {
                const wordPoint = card.querySelector(`.word-point[data-pair-id="${wordId}"]`);
                const imagePoint = card.querySelector(`.image-point[data-pair-id="${imageId}"]`);
                if (!wordPoint || !imagePoint) return;
                const from = getCenter(board, wordPoint);
                const to = getCenter(board, imagePoint);
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', from.x);
                line.setAttribute('y1', from.y);
                line.setAttribute('x2', to.x);
                line.setAttribute('y2', to.y);
                line.setAttribute('stroke', '#ef4444');
                line.setAttribute('stroke-width', '3');
                svg.appendChild(line);
            });

            if (dragState) {
                const from = getCenter(board, dragState.wordPoint);
                const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                line.setAttribute('x1', from.x);
                line.setAttribute('y1', from.y);
                line.setAttribute('x2', dragState.current.x);
                line.setAttribute('y2', dragState.current.y);
                line.setAttribute('stroke', '#3b82f6');
                line.setAttribute('stroke-width', '2');
                line.setAttribute('stroke-dasharray', '4 4');
                svg.appendChild(line);
            }
        };

        wordPoints.forEach((point) => {
            point.addEventListener('mousedown', (event) => {
                event.preventDefault();
                dragState = {
                    wordId: point.dataset.pairId,
                    wordPoint: point,
                    current: getCenter(board, point),
                };
                draw();
            });
        });

        document.addEventListener('mousemove', (event) => {
            if (!dragState) return;
            const boardRect = board.getBoundingClientRect();
            dragState.current = {
                x: event.clientX - boardRect.left,
                y: event.clientY - boardRect.top,
            };
            draw();
        });

        document.addEventListener('mouseup', (event) => {
            if (!dragState) return;
            const dropTarget = event.target.closest('.image-point');
            if (dropTarget && board.contains(dropTarget)) {
                const imageId = dropTarget.dataset.pairId;
                for (const [wordId, mappedImageId] of connections.entries()) {
                    if (mappedImageId === imageId) connections.delete(wordId);
                }
                connections.set(dragState.wordId, imageId);
                refreshAnswer();
            }
            dragState = null;
            draw();
        });

        imagePoints.forEach((point) => {
            point.addEventListener('click', () => {
                if (!dragState) return;
                const imageId = point.dataset.pairId;
                for (const [wordId, mappedImageId] of connections.entries()) {
                    if (mappedImageId === imageId) connections.delete(wordId);
                }
                connections.set(dragState.wordId, imageId);
                dragState = null;
                refreshAnswer();
                draw();
            });
        });

        window.addEventListener('resize', draw);
        draw();
    };

    list.querySelectorAll('.picture-match-card').forEach(initializePictureCard);

    const submitButton = document.getElementById('submitStudentAnswers');
    const answerStatus = document.getElementById('answerStatus');

    submitButton?.addEventListener('click', async () => {
        const cards = Array.from(document.querySelectorAll('.question-card'));
        const answers = cards.map((card) => {
            const questionId = Number(card.dataset.questionId || 0);
            const type = card.dataset.questionType || '';
            let answerValue = '';
            let focusEl = null;

            if (type === 'picture') {
                const answerInput = card.querySelector('.picture-answer');
                answerValue = answerInput?.value?.trim() || '';
                focusEl = card.querySelector('.match-board');
            } else if (type === 'rearrange') {
                answerValue = card.querySelector('.rearrange-answer')?.value?.trim() || '';
                focusEl = card.querySelector('.rearrange-drop');
            } else {
                const selected = card.querySelector('input[type="radio"]:checked');
                const generic = card.querySelector('.generic-answer');
                answerValue = selected ? selected.value : (generic?.value?.trim() || '');
                focusEl = selected ? null : (card.querySelector('input[type="radio"]') || generic);
            }

            return { question_id: questionId, type, answer_text: answerValue, focusEl };
        });

        const unanswered = answers.find((item) => item.answer_text === '' || item.answer_text.includes(',,') || item.answer_text.endsWith(','));
        if (unanswered) {
            answerStatus.textContent = 'Sila jawab semua soalan sebelum hantar.';
            answerStatus.className = 'text-center text-sm text-red-600';
            if (unanswered.focusEl) {
                setInvalid(unanswered.focusEl);
                unanswered.focusEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
            return;
        }

        answers.forEach((item) => clearInvalid(item.focusEl));

        const payload = {
            homework_id: <?php echo (int)$homeworkId; ?>,
            student_id: <?php echo isset($_SESSION['id']) ? (int)$_SESSION['id'] : 0; ?>,
            answers: answers.map(({ question_id, type, answer_text }) => ({ question_id, type, answer_text })),
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
