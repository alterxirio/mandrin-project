
<main class="max-w-6xl mx-auto px-6 py-8">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-6 space-y-8">
        <header class="space-y-2">
            <h1 class="text-2xl font-semibold text-gray-800">Tambah Soalan Dengar & Pilih Gambar</h1>
            <p class="text-gray-500">Muat naik audio dan pilih gambar jawapan yang betul.</p>
        </header>

        <section class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-4">
                <div>
                    <label for="instruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
                    <input id="instruction" type="text" placeholder="Contoh: Dengar audio dan pilih gambar yang betul."
                           class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                </div>

                <div>
                    <label for="audioUpload" class="block text-sm font-medium text-gray-700 mb-2">Fail Audio</label>
                    <div class="flex items-center gap-3">
                        <input id="audioUpload" type="file" accept="audio/*"
                               class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
                        <button id="playAudio" type="button"
                                class="inline-flex items-center rounded-lg border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50">
                            Play
                        </button>
                    </div>
                    <audio id="audioPreview" class="hidden"></audio>
                </div>

                <div class="rounded-xl border border-dashed border-gray-300 p-4 bg-gray-50">
                    <p class="text-xs font-semibold text-gray-500 mb-2">Nota</p>
                    <ul class="text-sm text-gray-500 space-y-1 list-disc list-inside">
                        <li>Masukkan audio seperti apel </li>
                        <li>Masukkan gambar pada pilihan jawapan seperti apel dan pisang </li>
                        <li>Pilih satu jawapan betul sahaja.</li>
                        <li>Cadangan: 3 hingga 4 pilihan.</li>
                        <li>Pastikan gambar jelas dan berkualiti.</li>
                    </ul>
                </div>
            </div>

            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-medium text-gray-700">Pilihan Jawapan</h2>
                    <button id="addChoice" type="button"
                            class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Pilihan</button>
                </div>

                <div id="choicesGrid" class="grid gap-4 sm:grid-cols-2"></div>
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
    const choicesGrid = document.getElementById("choicesGrid");
    const addChoice = document.getElementById("addChoice");
    const audioUpload = document.getElementById("audioUpload");
    const audioPreview = document.getElementById("audioPreview");
    const playAudio = document.getElementById("playAudio");

    const createChoiceCard = (index) => {
        const card = document.createElement("div");
        card.className = "rounded-xl border border-gray-200 p-4 bg-white shadow-sm space-y-3";

        const header = document.createElement("div");
        header.className = "flex items-center justify-between";

        const title = document.createElement("span");
        title.className = "text-sm font-semibold text-gray-700";
        title.textContent = `Pilihan ${index + 1}`;

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "text-xs text-gray-400 hover:text-red-500";
        removeButton.textContent = "Buang";
        removeButton.addEventListener("click", () => {
            card.remove();
            refreshChoiceLabels();
        });

        header.appendChild(title);
        header.appendChild(removeButton);

        const imagePreview = document.createElement("div");
        imagePreview.className = "h-32 w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-xs text-gray-400";
        imagePreview.textContent = "Pratonton Gambar";

        const imageInput = document.createElement("input");
        imageInput.type = "file";
        imageInput.accept = "image/*";
        imageInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        imageInput.addEventListener("change", () => {
            const file = imageInput.files[0];
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

        const labelInput = document.createElement("input");
        labelInput.type = "text";
        labelInput.placeholder = "Label pilihan (contoh: 苹果)";
        labelInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        const correctWrapper = document.createElement("label");
        correctWrapper.className = "flex items-center gap-2 text-sm text-gray-600";

        const correctRadio = document.createElement("input");
        correctRadio.type = "radio";
        correctRadio.name = "correctChoice";
        correctRadio.className = "text-red-600 focus:ring-red-500";

        const correctText = document.createElement("span");
        correctText.textContent = "Jawapan betul";

        correctWrapper.appendChild(correctRadio);
        correctWrapper.appendChild(correctText);

        card.appendChild(header);
        card.appendChild(imagePreview);
        card.appendChild(imageInput);
        card.appendChild(labelInput);
        card.appendChild(correctWrapper);

        return card;
    };

    const refreshChoiceLabels = () => {
        Array.from(choicesGrid.children).forEach((card, index) => {
            const title = card.querySelector("span");
            if (title) {
                title.textContent = `Pilihan ${index + 1}`;
            }
        });
    };

    const addDefaultChoices = () => {
        for (let i = 0; i < 2; i += 1) {
            choicesGrid.appendChild(createChoiceCard(i));
        }
    };

    addChoice.addEventListener("click", () => {
        const index = choicesGrid.children.length;
        choicesGrid.appendChild(createChoiceCard(index));
    });

    audioUpload.addEventListener("change", () => {
        const file = audioUpload.files[0];
        if (!file) {
            audioPreview.src = "";
            return;
        }
        audioPreview.src = URL.createObjectURL(file);
    });

    playAudio.addEventListener("click", () => {
        if (!audioPreview.src) {
            return;
        }
        audioPreview.play();
    });

    addDefaultChoices();
</script> -->