<div class="space-y-6">
  <header class="space-y-2">
    <h2 class="text-2xl font-semibold text-gray-800">Tambah Soalan Seret & Lepas</h2>
    <p class="text-gray-500">Susun perkataan untuk membentuk ayat yang betul.</p>
  </header>

  <section class="grid gap-6 md:grid-cols-2">
    <div class="space-y-4">
      <div>
        <label for="ddInstruction" class="block text-sm font-medium text-gray-700 mb-2">Arahan Soalan</label>
        <input id="ddInstruction" name="drag_instruction" type="text"
               placeholder="Contoh: Susun perkataan ini menjadi ayat yang betul."
               class="w-full rounded-lg border border-gray-300 bg-gray-50 px-4 py-2 text-sm focus:border-red-500 focus:ring-red-500">
      </div>

      <div>
        <div class="flex items-center justify-between mb-2">
          <label class="block text-sm font-medium text-gray-700">Senarai Perkataan</label>
          <button id="ddAddWordRow" type="button"
                  class="text-sm font-semibold text-red-600 hover:text-red-700">+ Tambah Perkataan</button>
        </div>

        <div id="ddWordInputs" class="space-y-2">
          <div class="flex items-center gap-2">
            <input type="text" name="drag_words[]" placeholder="Perkataan 1"
                   class="dd-word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
            <button type="button" class="dd-remove-word text-gray-400 hover:text-red-500">✕</button>
          </div>
          <div class="flex items-center gap-2">
            <input type="text" name="drag_words[]" placeholder="Perkataan 2"
                   class="dd-word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
            <button type="button" class="dd-remove-word text-gray-400 hover:text-red-500">✕</button>
          </div>
          <div class="flex items-center gap-2">
            <input type="text" name="drag_words[]" placeholder="Perkataan 3"
                   class="dd-word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500">
            <button type="button" class="dd-remove-word text-gray-400 hover:text-red-500">✕</button>
          </div>
        </div>
      </div>
    </div>

    <div class="space-y-4">
      <div class="flex items-center justify-between">
        <h3 class="text-sm font-medium text-gray-700">Pratonton Seret & Lepas</h3>
        <button id="ddResetPreview" type="button"
                class="text-sm font-semibold text-red-600 hover:text-red-700">Reset</button>
      </div>

      <div class="bg-gray-50 border border-dashed border-gray-300 rounded-xl p-4 space-y-4">
        <div>
          <p class="text-xs font-semibold text-gray-500 mb-2">Perkataan Boleh Seret</p>
          <div id="ddDraggableWords" class="flex flex-wrap gap-2"></div>
        </div>

        <div>
          <p class="text-xs font-semibold text-gray-500 mb-2">Slot Jawapan</p>
          <div id="ddDropSlots" class="flex flex-wrap gap-3"></div>
        </div>
      </div>
    </div>
  </section>

  <input type="hidden" id="ddAnswerSentence" name="drag_answer">

  <div class="flex justify-end">
    <button type="submit"
            class="inline-flex items-center justify-center rounded-lg bg-red-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-red-700">
      Simpan Soalan
    </button>
  </div>
</div>

<script>
  document.addEventListener("DOMContentLoaded", () => {
    const wordInputs = document.getElementById("ddWordInputs");
    const addWordRow = document.getElementById("ddAddWordRow");
    const draggableWords = document.getElementById("ddDraggableWords");
    const dropSlots = document.getElementById("ddDropSlots");
    const resetPreview = document.getElementById("ddResetPreview");
    const answerSentence = document.getElementById("ddAnswerSentence");

    if (!wordInputs || !addWordRow || !draggableWords || !dropSlots || !resetPreview || !answerSentence) {
      return;
    }

    const createWordRow = (placeholderText = "Perkataan") => {
      const wrapper = document.createElement("div");
      wrapper.className = "flex items-center gap-2";

      const input = document.createElement("input");
      input.type = "text";
      input.name = "drag_words[]";
      input.placeholder = placeholderText;
      input.className = "dd-word-input w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
      input.addEventListener("input", renderPreview);

      const removeButton = document.createElement("button");
      removeButton.type = "button";
      removeButton.className = "dd-remove-word text-gray-400 hover:text-red-500";
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
      const words = Array.from(wordInputs.querySelectorAll(".dd-word-input"))
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

    wordInputs.querySelectorAll(".dd-word-input").forEach((input) => {
      input.addEventListener("input", renderPreview);
    });
    wordInputs.querySelectorAll(".dd-remove-word").forEach((button) => {
      button.addEventListener("click", () => {
        button.parentElement.remove();
        renderPreview();
      });
    });

    resetPreview.addEventListener("click", renderPreview);

    renderPreview();
  });
</script>
