<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>True/False Image Question</title>

    <link rel="stylesheet" href="../css/work.php">
    <?php include("header.php"); ?>

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
</head>

<body class="bg-gray-100 min-h-screen">
<?php include("navbar.php"); ?>

<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Betul / Salah Berdasarkan Gambar</h1>
            <p class="text-gray-500">Muat naik gambar dan tulis pernyataan untuk dinilai betul atau salah.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="space-y-4">
                <div>
                    <label for="tfInstruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
                    <input id="tfInstruction" type="text" placeholder="Contoh: Lihat gambar dan tentukan pernyataan betul atau salah."
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="tfImageUpload" class="block text-sm font-medium text-gray-700 mb-2">Gambar Soalan</label>
                    <div class="space-y-2">
                        <div id="tfImagePreview" class="h-40 rounded-xl border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-xs text-gray-400">
                            Pratonton Gambar
                        </div>
                        <input id="tfImageUpload" type="file" accept="image/*"
                               class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                    </div>
                </div>

                <div class="rounded-xl border border-dashed border-gray-300 p-4 bg-gray-50 space-y-2">
                    <p class="text-xs font-semibold text-gray-500">Nota</p>
                    <ul class="text-sm text-gray-500 space-y-1 list-disc list-inside">
                        <li>Gunakan pernyataan yang mudah difahami.</li>
                        <li>Pastikan jawapan betul/salah jelas.</li>
                        <li>Contoh: "Gambar menunjukkan buah epal."</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label for="tfStatement" class="block text-sm font-medium text-gray-700 mb-2">Pernyataan</label>
                    <textarea id="tfStatement" rows="5" placeholder="Tulis pernyataan berdasarkan gambar..."
                              class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                </div>

                <div class="rounded-xl border border-gray-200 p-4 bg-white shadow-sm space-y-3">
                    <p class="text-sm font-semibold text-gray-700">Jawapan Betul</p>
                    <div class="flex flex-col gap-2">
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="radio" name="tfCorrect" class="text-red-600 focus:ring-red-500">
                            Betul
                        </label>
                        <label class="flex items-center gap-2 text-sm text-gray-600">
                            <input type="radio" name="tfCorrect" class="text-red-600 focus:ring-red-500">
                            Salah
                        </label>
                    </div>
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
    document.addEventListener("DOMContentLoaded", () => {
        const imageUpload = document.getElementById("tfImageUpload");
        const imagePreview = document.getElementById("tfImagePreview");

        if (!imageUpload || !imagePreview) {
            return;
        }

        imageUpload.addEventListener("change", () => {
            const file = imageUpload.files[0];
            if (!file) {
                imagePreview.textContent = "Pratonton Gambar";
                imagePreview.style.backgroundImage = "";
                return;
            }

            const reader = new FileReader();
            reader.onload = (event) => {
                imagePreview.textContent = "";
                imagePreview.style.backgroundImage = `url('${event.target.result}')`;
                imagePreview.style.backgroundSize = "cover";
                imagePreview.style.backgroundPosition = "center";
            };
            reader.readAsDataURL(file);
        });
    });
</script>

</body>
</html>
