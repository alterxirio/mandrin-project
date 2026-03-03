function AudioImageInit(container) {
    const choicesGrid = container.querySelector("#choicesGrid");
    const addChoice = container.querySelector("#addChoice");
    const audioUpload = container.querySelector("#audioUpload");
    const audioPreview = container.querySelector("#audioPreview");
    const playAudio = container.querySelector("#playAudio");

    if (!choicesGrid || !addChoice) return;

    const refreshChoiceLabels = () => {
        Array.from(choicesGrid.children).forEach((card,index)=>{
            const title = card.querySelector("span");
            if(title) title.textContent = `Pilihan ${index+1}`;
        });
    };

    const createChoiceCard = (index) => {
        const card = document.createElement("div");
        card.className = "rounded-xl border border-gray-200 p-4 bg-white shadow-sm space-y-3";

        const header = document.createElement("div");
        header.className = "flex items-center justify-between";

        const title = document.createElement("span");
        title.className = "text-sm font-semibold text-gray-700";
        title.textContent = `Pilihan ${index+1}`;

        const removeButton = document.createElement("button");
        removeButton.type="button";
        removeButton.className="text-xs text-gray-400 hover:text-red-500";
        removeButton.textContent="Buang";
        removeButton.addEventListener("click", ()=>{
            card.remove();
            refreshChoiceLabels();
        });

        header.append(title,removeButton);

        const imagePreview = document.createElement("div");
        imagePreview.className="h-32 w-full rounded-lg border border-dashed border-gray-300 bg-gray-50 flex items-center justify-center text-xs text-gray-400";
        imagePreview.textContent="Pratonton Gambar";

        const imageInput = document.createElement("input");
        imageInput.type="file";
        imageInput.accept="image/*";
        imageInput.className="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        imageInput.addEventListener("change",()=>{
            const file=imageInput.files[0];
            if(!file){ imagePreview.textContent="Pratonton Gambar"; imagePreview.style.backgroundImage=""; return; }
            const reader=new FileReader();
            reader.onload=(e)=>{
                imagePreview.textContent="";
                imagePreview.style.backgroundImage=`url('${e.target.result}')`;
                imagePreview.style.backgroundSize="cover";
                imagePreview.style.backgroundPosition="center";
            };
            reader.readAsDataURL(file);
        });

        const labelInput=document.createElement("input");
        labelInput.type="text";
        labelInput.placeholder="Label pilihan (contoh: 苹果)";
        labelInput.className="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        const correctWrapper=document.createElement("label");
        correctWrapper.className="flex items-center gap-2 text-sm text-gray-600";

        const correctRadio=document.createElement("input");
        correctRadio.type="radio";
        correctRadio.name=`correctChoice-${container.dataset.formId || ""}`;
        correctRadio.className="text-red-600 focus:ring-red-500";

        const correctText=document.createElement("span");
        correctText.textContent="Jawapan betul";

        correctWrapper.append(correctRadio, correctText);
        card.append(header,imagePreview,imageInput,labelInput,correctWrapper);
        return card;
    };

    const addDefaultChoices = () => { for(let i=0;i<2;i++) choicesGrid.appendChild(createChoiceCard(i)); };

    addChoice.addEventListener("click",()=>{
        const index=choicesGrid.children.length;
        choicesGrid.appendChild(createChoiceCard(index));
    });

    if(audioUpload){
        audioUpload.addEventListener("change",()=>{
            const file = audioUpload.files[0];
            audioPreview.src = file ? URL.createObjectURL(file) : "";
        });
    }
    if(playAudio){
        playAudio.addEventListener("click",()=>{ if(audioPreview.src) audioPreview.play(); });
    }

    addDefaultChoices();
}