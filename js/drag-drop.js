function DragDropInit(container) {
    const wordInputs = container.querySelector("#wordInputs");
    const addWordRow = container.querySelector("#addWordRow");
    const draggableWords = container.querySelector("#draggableWords");
    const dropSlots = container.querySelector("#dropSlots");
    const resetPreview = container.querySelector("#resetPreview");
    const answerSentence = container.querySelector("#answerSentence");

    if (!wordInputs) return;

    const createWordRow = (text="Perkataan") => {
        const wrapper = document.createElement("div");
        wrapper.className = "flex items-center gap-2";

        const input = document.createElement("input");
        input.className = "word-input w-full rounded-lg border px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        input.placeholder = text;
        input.addEventListener("input", renderPreview);

        const remove = document.createElement("button");
        remove.type = "button";
        remove.className = "remove-word text-gray-400 hover:text-red-500";
        remove.textContent = "✕";
        remove.onclick = () => { wrapper.remove(); renderPreview(); };

        wrapper.append(input, remove);
        return wrapper;
    };

    const renderPreview = () => {
        const words = Array.from(wordInputs.querySelectorAll(".word-input"))
            .map(i => i.value.trim())
            .filter(v => v.length);

        draggableWords.innerHTML = "";
        dropSlots.innerHTML = "";

        words.forEach(word => {
            const pill = document.createElement("button");
            pill.textContent = word;
            pill.draggable = true;
            pill.addEventListener("dragstart", e => e.dataTransfer.setData("text/plain", word));
            draggableWords.appendChild(pill);

            const slot = document.createElement("div");
            slot.textContent = "Drop";
            slot.className = "min-w-[80px] h-10 border-2 border-dashed rounded flex items-center justify-center text-gray-400";
            slot.addEventListener("dragover", e => e.preventDefault());
            slot.addEventListener("drop", e => {
                slot.textContent = e.dataTransfer.getData("text/plain");
                updateAnswer();
            });
            dropSlots.appendChild(slot);
        });

        updateAnswer();
    };

    const updateAnswer = () => {
        answerSentence.value = Array.from(dropSlots.children)
            .map(s => s.textContent)
            .filter(t => t !== "Drop")
            .join(" ");
    };

    addWordRow.addEventListener("click", () => {
        wordInputs.appendChild(createWordRow(`Perkataan ${wordInputs.children.length+1}`));
    });

    resetPreview.addEventListener("click", renderPreview);

    renderPreview();
}
