<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Image Question</title>

    <link rel="stylesheet" href="../css/work.php">
    <?php include("../frontend/header.php"); ?>


    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
<?php include("../frontend/navbar.php"); ?>

<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Padankan Perkataan & Gambar</h1>
            <p class="text-gray-500">Tambah perkataan dan gambar untuk dipadankan oleh pelajar.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="space-y-4">
                <div>
                    <label for="instruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
                    <input id="instruction" type="text" placeholder="Contoh: Padankan perkataan dengan gambar yang betul."
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div class="rounded-xl border border-dashed border-gray-300 p-4 bg-gray-50 space-y-2">
                    <p class="text-xs font-semibold text-gray-500">Nota</p>
                    <ul class="text-sm text-gray-500 space-y-1 list-disc list-inside">
                        <li>Cadangan: 4 hingga 6 pasangan.</li>
                        <li>Gunakan gambar yang jelas untuk mengelakkan kekeliruan.</li>
                        <li>Label perkataan boleh jadi Mandarin, pinyin, atau terjemahan.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-700">Senarai Padanan</h2>
                    <button id="addPair" type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Pasangan</button>
                </div>

                <div id="pairList" class="space-y-4"></div>
            </div>
        </section>

        <section class="space-y-3">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-medium text-gray-700">Pratonton Padanan</h2>
                <button id="refreshPreview" type="button"
                        class="text-sm font-semibold text-red-600 hover:text-red-700">Reset</button>
            </div>

            <div class="grid gap-4 md:grid-cols-2 bg-gray-50 border border-dashed border-gray-300 rounded-xl p-4">
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-2">Kolum Perkataan</p>
                    <div id="wordPreview" class="space-y-2"></div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-gray-500 mb-2">Kolum Gambar</p>
                    <div id="imagePreview" class="space-y-2"></div>
                </div>
            </div>
        </section>

        <footer class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <button type="button"
                    class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                Simpan Soalan
            </button>
        </footer>
    </div>
</main>

<script>
    const pairList = document.getElementById("pairList");
    const addPair = document.getElementById("addPair");
    const wordPreview = document.getElementById("wordPreview");
    const imagePreview = document.getElementById("imagePreview");
    const refreshPreview = document.getElementById("refreshPreview");

    const createPairRow = (index) => {
        const row = document.createElement("div");
        row.className = "grid gap-3 md:grid-cols-[2fr,2fr,auto] items-start border border-gray-200 rounded-xl p-4 bg-white shadow-sm";

        const wordInput = document.createElement("input");
        wordInput.type = "text";
        wordInput.placeholder = "Perkataan (contoh: 苹果)";
        wordInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        wordInput.addEventListener("input", renderPreview);

        const imageWrapper = document.createElement("div");
        imageWrapper.className = "space-y-2";

        const imagePreviewBox = document.createElement("div");
        imagePreviewBox.className = "h-24 w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-xs text-gray-400";
        imagePreviewBox.textContent = "Pratonton Gambar";

        const imageInput = document.createElement("input");
        imageInput.type = "file";
        imageInput.accept = "image/*";
        imageInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
            if (!file) {
                imagePreviewBox.textContent = "Pratonton Gambar";
                imagePreviewBox.style.backgroundImage = "";
                renderPreview();
                return;
            }
            const reader = new FileReader();
            reader.onload = (event) => {
                imagePreviewBox.textContent = "";
                imagePreviewBox.style.backgroundImage = `url('${event.target.result}')`;
                imagePreviewBox.style.backgroundSize = "cover";
                imagePreviewBox.style.backgroundPosition = "center";
                renderPreview();
            };
            reader.readAsDataURL(file);
        });

        imageWrapper.appendChild(imagePreviewBox);
        imageWrapper.appendChild(imageInput);

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "text-xs text-gray-400 hover:text-red-500 mt-2";
        removeButton.textContent = "Buang";
        removeButton.addEventListener("click", () => {
            row.remove();
            renderPreview();
        });

        row.appendChild(wordInput);
        row.appendChild(imageWrapper);
        row.appendChild(removeButton);

        row.dataset.index = index;
        return row;
    };

    const renderPreview = () => {
        wordPreview.innerHTML = "";
        imagePreview.innerHTML = "";

        const rows = Array.from(pairList.children);
        rows.forEach((row, index) => {
            const wordValue = row.querySelector("input[type='text']").value.trim();
            const imageBox = row.querySelector("div.h-24");

            const wordItem = document.createElement("div");
            wordItem.className = "px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm text-gray-700 shadow-sm";
            wordItem.textContent = wordValue || `Perkataan ${index + 1}`;

            const imageItem = document.createElement("div");
            imageItem.className = "h-16 rounded-lg border border-gray-200 bg-white shadow-sm flex items-center justify-center text-xs text-gray-400 overflow-hidden";

            if (imageBox && imageBox.style.backgroundImage) {
                imageItem.style.backgroundImage = imageBox.style.backgroundImage;
                imageItem.style.backgroundSize = "cover";
                imageItem.style.backgroundPosition = "center";
                imageItem.textContent = "";
            } else {
                imageItem.textContent = "Gambar";
            }

            wordPreview.appendChild(wordItem);
            imagePreview.appendChild(imageItem);
        });
    };

    const addDefaultPairs = () => {
        for (let i = 0; i < 3; i += 1) {
            pairList.appendChild(createPairRow(i));
        }
        renderPreview();
    };

    addPair.addEventListener("click", () => {
        pairList.appendChild(createPairRow(pairList.children.length));
        renderPreview();
    });

    refreshPreview.addEventListener("click", renderPreview);

    addDefaultPairs();
</script>

</body>
</html>