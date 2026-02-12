<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Drag & Drop Question</title>

    <link rel="stylesheet" href="../css/work.php">
    <?php include("header.php"); ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
<?php include("navbar.php"); ?>

<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Seret & Lepas</h1>
            <p class="text-gray-500">Susun perkataan untuk membentuk ayat yang betul.</p>
        </header>

        <section class="grid gap-6 md:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label for="instruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
                    <input id="instruction" type="text" placeholder="Contoh: Susun perkataan ini menjadi ayat yang betul."
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-sm font-medium text-gray-700">Senarai Perkataan</label>
                        <button id="addWordRow" type="button"
                                class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Perkataan</button>
                    </div>

                    <div id="wordInputs" class="space-y-2">
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Perkataan 1"
                                   class="word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                            <button type="button" class="remove-word text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Perkataan 2"
                                   class="word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                            <button type="button" class="remove-word text-gray-400 hover:text-red-500">✕</button>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="text" placeholder="Perkataan 3"
                                   class="word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                            <button type="button" class="remove-word text-gray-400 hover:text-red-500">✕</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-700">Pratonton Seret & Lepas</h2>
                    <button id="resetPreview" type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700">Reset</button>
                </div>

                <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-4 space-y-4">
                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-2">Perkataan Boleh Seret</p>
                        <div id="draggableWords" class="flex flex-wrap gap-2"></div>
                    </div>

                    <div>
                        <p class="text-xs font-semibold text-gray-500 mb-2">Slot Jawapan</p>
                        <div id="dropSlots" class="flex flex-wrap gap-3"></div>
                    </div>
                </div>
            </div>
        </section>

        <footer class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <input type="hidden" id="answerSentence">
            <button type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                Simpan Soalan
            </button>
        </footer>
    </div>
</main>

<script>
    const wordInputs = document.getElementById("wordInputs");
    const addWordRow = document.getElementById("addWordRow");
    const draggableWords = document.getElementById("draggableWords");
    const dropSlots = document.getElementById("dropSlots");
    const resetPreview = document.getElementById("resetPreview");
    const answerSentence = document.getElementById("answerSentence");

    const createWordRow = (placeholderText = "Perkataan") => {
        const wrapper = document.createElement("div");
        wrapper.className = "flex items-center gap-2";

        const input = document.createElement("input");
        input.type = "text";
        input.placeholder = placeholderText;
        input.className = "word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        input.addEventListener("input", renderPreview);

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "remove-word text-gray-400 hover:text-red-500";
        removeButton.textContent = "✕";
        removeButton.addEventListener("click", () => {
            wrapper.remove();
            renderPreview();
        });

        wrapper.appendChild(input);
        wrapper.appendChild(removeButton);
        return wrapper;
    };

    const renderPreview = () => {
        const words = Array.from(wordInputs.querySelectorAll(".word-input"))
            .map((input) => input.value.trim())
            .filter((value) => value.length > 0);

        draggableWords.innerHTML = "";
        dropSlots.innerHTML = "";

        words.forEach((word, index) => {
            const pill = document.createElement("button");
            pill.type = "button";
            pill.textContent = word;
            pill.draggable = true;
            pill.dataset.word = word;
            pill.className = "px-3 py-1 rounded-full bg-white border border-gray-300 text-sm shadow-sm cursor-move hover:border-red-400";
            pill.addEventListener("dragstart", (event) => {
                event.dataTransfer.setData("text/plain", word);
                event.dataTransfer.effectAllowed = "move";
            });

            draggableWords.appendChild(pill);

            const slot = document.createElement("div");
            slot.dataset.index = index;
            slot.className = "min-w-[120px] h-10 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center text-sm text-gray-400 bg-white";
            slot.textContent = "Drop";
            slot.addEventListener("dragover", (event) => {
                event.preventDefault();
                slot.classList.add("border-red-400");
            });
            slot.addEventListener("dragleave", () => {
                slot.classList.remove("border-red-400");
            });
            slot.addEventListener("drop", (event) => {
                event.preventDefault();
                const droppedWord = event.dataTransfer.getData("text/plain");
                slot.textContent = droppedWord;
                slot.classList.remove("border-red-400");
                slot.classList.add("border-red-500", "text-gray-700");
                updateAnswerSentence();
            });

            dropSlots.appendChild(slot);
        });

        updateAnswerSentence();
    };

    const updateAnswerSentence = () => {
        const sentence = Array.from(dropSlots.children)
            .map((slot) => slot.textContent)
            .filter((word) => word && word !== "Drop")
            .join(" ");
        answerSentence.value = sentence;
    };

    addWordRow.addEventListener("click", () => {
        const newRow = createWordRow(`Perkataan ${wordInputs.children.length + 1}`);
        wordInputs.appendChild(newRow);
    });

    wordInputs.querySelectorAll(".word-input").forEach((input) => {
        input.addEventListener("input", renderPreview);
    });
    wordInputs.querySelectorAll(".remove-word").forEach((button) => {
        button.addEventListener("click", () => {
            button.parentElement.remove();
            renderPreview();
        });
    });

    resetPreview.addEventListener("click", renderPreview);

    renderPreview();
</script>

</body>
</html>
