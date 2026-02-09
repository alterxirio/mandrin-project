<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Match Image Question</title>

    <link rel="stylesheet" href="../css/work.php">
    <?php include("header.php"); ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
<?php include("navbar.php"); ?>

<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Padankan Perkataan & Gambar</h1>
            <p class="text-gray-500">Tambah perkataan dan gambar untuk dipadankan oleh pelajar.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="space-y-4">
                <div>
                    <label for="matchInstruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
                    <input id="matchInstruction" type="text" placeholder="Contoh: Padankan perkataan dengan gambar yang betul."
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
                    <button id="matchAddPair" type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Pasangan</button>
                </div>

                <div id="matchPairList" class="space-y-4"></div>
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
document.addEventListener("DOMContentLoaded", () => {
    const pairList = document.getElementById("matchPairList");
    const addPair = document.getElementById("matchAddPair");

    if (!pairList || !addPair) {
        return;
    }

    const createPairRow = (index) => {
        const row = document.createElement("div");
        row.className = "grid gap-3 md:grid-cols-[2fr,2fr,auto] items-start border border-gray-200 rounded-xl p-4 bg-white shadow-sm";

        const wordInput = document.createElement("input");
        wordInput.type = "text";
        wordInput.placeholder = "Perkataan (contoh: 苹果)";
        wordInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        const imageWrapper = document.createElement("div");
        imageWrapper.className = "space-y-2";

        const imageInput = document.createElement("input");
        imageInput.type = "file";
        imageInput.accept = "image/*";
        imageInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        imageWrapper.appendChild(imageInput);

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "text-xs text-gray-400 hover:text-red-500 mt-2";
        removeButton.textContent = "Buang";
        removeButton.addEventListener("click", () => {
            row.remove();
        });

        row.appendChild(wordInput);
        row.appendChild(imageWrapper);
        row.appendChild(removeButton);

        row.dataset.index = index;
        return row;
    };

    const addDefaultPairs = () => {
        for (let i = 0; i < 3; i++) {
            pairList.appendChild(createPairRow(i));
        }
    };

    addPair.addEventListener("click", () => {
        pairList.appendChild(createPairRow(pairList.children.length));
    });

    addDefaultPairs();
});
</script>



</body>
</html>