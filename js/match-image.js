function MatchImageInit(container){
    const pairList = container.querySelector("#matchPairList");
    const addPair = container.querySelector("#matchAddPair");
    if(!pairList || !addPair) return;

    const createPairRow = (index)=>{
        const row = document.createElement("div");
        row.className="grid gap-3 md:grid-cols-[2fr,2fr,auto] items-start border border-gray-200 rounded-xl p-4 bg-white shadow-sm";

        const wordInput=document.createElement("input");
        wordInput.type="text";
        wordInput.placeholder="Perkataan (contoh: 苹果)";
        wordInput.className="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";

        const imageWrapper=document.createElement("div");
        const imageInput=document.createElement("input");
        imageInput.type="file";
        imageInput.accept="image/*";
        imageInput.className="w-full rounded-lg border border-gray-300 bg-gray-50 px-3 py-2 text-sm focus:border-red-500 focus:ring-red-500";
        imageWrapper.appendChild(imageInput);

        const removeButton=document.createElement("button");
        removeButton.type="button";
        removeButton.className="text-xs text-gray-400 hover:text-red-500 mt-2";
        removeButton.textContent="Buang";
        removeButton.addEventListener("click",()=>row.remove());

        row.append(wordInput,imageWrapper,removeButton);
        row.dataset.index=index;
        return row;
    };

    addPair.addEventListener("click",()=>pairList.appendChild(createPairRow(pairList.children.length)));

    // add 3 default pairs
    for(let i=0;i<3;i++) pairList.appendChild(createPairRow(i));
}