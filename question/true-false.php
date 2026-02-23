<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan MCQ (Teks Sahaja)</h1>
            <p class="text-gray-500">Tulis soalan dan bina pilihan jawapan.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-[2fr,3fr]">
            <div class="space-y-4">

                <div>
                    <label for="mcqQuestion" class="block text-sm font-medium text-gray-700 mb-2">Soalan</label>
                    <textarea id="mcqQuestion" rows="5" placeholder="Tulis soalan di sini..." class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500"></textarea>
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
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-700">Pilihan Jawapan</h2>
                    <button id="mcqAddChoice" type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Pilihan</button>
                </div>

                <div id="mcqChoicesGrid" class="grid md:grid-cols-2 gap-4">
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