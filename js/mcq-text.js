function MCQInit(container) {
    const choicesGrid = container.querySelector("#mcqChoicesGrid");
    const addChoice = container.querySelector("#mcqAddChoice");
    if (!choicesGrid || !addChoice) return;

    const refreshChoiceLabels = () => {
        Array.from(choicesGrid.children).forEach((card, index) => {
            const title = card.querySelector("[data-mcq-title]");
            if (title) title.textContent = `Pilihan ${index+1}`;
        });
    };

    const createChoiceCard = (index) => {
        const card = document.createElement("div");
        card.className = "rounded-xl border border-gray-200 p-4 bg-white shadow-sm space-y-3";

        const header = document.createElement("div");
        header.className = "flex items-center justify-between";

        const title = document.createElement("span");
        title.dataset.mcqTitle = "true";
        title.className = "text-sm font-semibold text-gray-700";
        title.textContent = `Pilihan ${index+1}`;

        const removeButton = document.createElement("button");
        removeButton.type = "button";
        removeButton.className = "text-xs text-gray-400 hover:text-red-500";
        removeButton.textContent = "Buang";
        removeButton.addEventListener("click", () => {
            card.remove();
            refreshChoiceLabels();
        });

        header.append(title, removeButton);

        const choiceInput = document.createElement("input");
        choiceInput.type = "text";
        choiceInput.placeholder = "Tulis jawapan di sini...";
        choiceInput.className = "w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        const correctWrapper = document.createElement("label");
        correctWrapper.className = "flex items-center gap-2 text-sm text-gray-600";

        const correctRadio = document.createElement("input");
        correctRadio.type = "radio";
        correctRadio.name = `mcqCorrectChoice-${container.dataset.formId || ""}`;
        correctRadio.className = "text-red-600 focus:ring-red-500";

        const correctText = document.createElement("span");
        correctText.textContent = "Jawapan betul";

        correctWrapper.append(correctRadio, correctText);
        card.append(header, choiceInput, correctWrapper);
        return card;
    };

    const addDefaultChoices = () => {
        for (let i=0;i<3;i++) choicesGrid.appendChild(createChoiceCard(i));
    };

    addChoice.addEventListener("click", () => {
        const index = choicesGrid.children.length;
        choicesGrid.appendChild(createChoiceCard(index));
    });

    addDefaultChoices();
}
