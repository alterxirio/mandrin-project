function DragDropInit(container) {
    const wordInputs = container.querySelector("#wordInputs");
    const addWordRow = container.querySelector("#addWordRow");
    const draggableWords = container.querySelector("#draggableWords");
    const dropSlots = container.querySelector("#dropSlots");
    const resetPreview = container.querySelector("#resetPreview");
    const answerSentence = container.querySelector("#answerSentence");

    // prevent double initialization
    if (!wordInputs || wordInputs.dataset.initialized === "true") return;
    wordInputs.dataset.initialized = "true";

    /* ===============================
       CREATE WORD ROW
    =============================== */
    const createWordRow = (text = "Perkataan") => {
        const wrapper = document.createElement("div");
        wrapper.className = "flex items-center gap-2";

        const input = document.createElement("input");
        input.className =
            "word-input w-full rounded-lg border px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        input.required = true;
        input.placeholder = text;

        const remove = document.createElement("button");
        remove.type = "button";
        remove.className = "remove-word text-gray-400 hover:text-red-500";
        remove.textContent = "✕";

        wrapper.append(input, remove);
        return wrapper;
    };

    /* ===============================
       RENDER PREVIEW
    =============================== */
    const renderPreview = () => {
        if (!draggableWords || !dropSlots) return;

        const words = Array.from(
            wordInputs.querySelectorAll(".word-input")
        )
            .map(i => i.value.trim())
            .filter(v => v.length);

        draggableWords.innerHTML = "";
        dropSlots.innerHTML = "";

        words.forEach(word => {

            // draggable word
            const pill = document.createElement("button");
            pill.type = "button";
            pill.textContent = word;
            pill.draggable = true;

            pill.addEventListener("dragstart", e => {
                e.dataTransfer.setData("text/plain", word);
            });

            draggableWords.appendChild(pill);

            // drop slot
            const slot = document.createElement("div");
            slot.textContent = "Drop";
            slot.className =
                "min-w-[80px] h-10 border-2 border-dashed rounded flex items-center justify-center text-gray-400";

            slot.addEventListener("dragover", e => e.preventDefault());

            slot.addEventListener("drop", e => {
                e.preventDefault();
                slot.textContent =
                    e.dataTransfer.getData("text/plain");
                updateAnswer();
            });

            dropSlots.appendChild(slot);
        });

        updateAnswer();
    };

    /* ===============================
       UPDATE ANSWER
    =============================== */
    const updateAnswer = () => {
        if (!dropSlots || !answerSentence) return;

        answerSentence.value = Array.from(dropSlots.children)
            .map(s => s.textContent)
            .filter(t => t !== "Drop")
            .join(" ");
    };

    /* ===============================
       DELETE WORD (EVENT DELEGATION)
       WORKS FOR DEFAULT + NEW ROWS
    =============================== */
    wordInputs.addEventListener("click", (event) => {
        const removeBtn = event.target.closest(".remove-word");
        if (!removeBtn) return;

        const row = removeBtn.closest(".flex");
        if (row) {
            row.remove();
            renderPreview();
        }
    });

    /* ===============================
       INPUT CHANGE (LIVE UPDATE)
    =============================== */
    wordInputs.addEventListener("input", (event) => {
        if (!event.target.classList.contains("word-input")) return;
        renderPreview();
    });

    /* ===============================
       ADD NEW WORD
    =============================== */
    if (addWordRow) {
        addWordRow.addEventListener("click", () => {
            wordInputs.appendChild(
                createWordRow(
                    `Perkataan ${wordInputs.children.length + 1}`
                )
            );
        });
    }

    /* ===============================
       RESET PREVIEW
    =============================== */
    if (resetPreview) {
        resetPreview.addEventListener("click", renderPreview);
    }

    // first render
    renderPreview();
}