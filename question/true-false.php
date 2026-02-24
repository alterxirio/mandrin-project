<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Betul / Salah</h1>
            <p class="text-gray-500">Tulis soalan dan pilih jawapan yang betul.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="space-y-4">
                <div>
                    <label for="tfQuestion" class="block text-sm font-medium text-gray-700 mb-2">Soalan</label>
                    <textarea id="tfQuestion" rows="5" placeholder="Tulis soalan di sini..." class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
                </div>

                <div>
                   <label for="tfImageUpload" class="block text-sm font-medium text-gray-700 mb-2">Muat naik gambar (pilihan)</label>
                    <input id="tfImageUpload" type="file" accept="image/*" class="block w-full text-sm text-gray-700 file:mr-4 file:rounded-lg file:border-0 file:bg-red-50 file:px-4 file:py-2 file:font-semibold file:text-red-700 hover:file:bg-red-100">
                </div>

                <div class="rounded-xl border border-dashed border-gray-300 p-4 bg-gray-50 space-y-2">
                    <p class="text-xs font-semibold text-gray-500">Nota</p>
                    <ul class="text-sm text-gray-500 space-y-1 list-disc list-inside">
                        <li>Gunakan 3 hingga 4 pilihan jawapan.</li>
                        <li>Masukkan hanya satu jawapan betul di dalam kotak pilihan.</li>
                        <li>Masukkan jawapan salah dalam kotak pilihan yang lain.</li>
                        <li>Pastikan soalan jelas dan ringkas.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <h2 class="text-sm font-medium text-gray-700">Pratonton Gambar</h2>
                <div id="tfImagePreview" class="h-56 rounded-xl border border-gray-300 bg-gray-100 grid place-items-center text-gray-400 text-sm">
                      Pratonton Gambar
                </div>

                <div class="rounded-xl border border-gray-200 p-4 bg-white shadow-sm space-y-3">
                    <p class="text-sm font-semibold text-gray-700">Jawapan Betul</p>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="tfCorrectAnswer" value="true" class="text-red-600 focus:ring-red-500">
                        Betul
                    </label>

                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="tfCorrectAnswer" value="false" class="text-red-600 focus:ring-red-500">
                        Salah
                    </label>
                </div>
            </div>
        </section>

        <footer class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <button type="button" class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
                Simpan Soalan
            </button>
        </footer>
    </div>
</main>

<!-- <script>
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
</script> -->