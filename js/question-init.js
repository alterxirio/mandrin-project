document.querySelectorAll(".bg-white.rounded-2xl").forEach((form,index)=>{
    form.dataset.formId = index; // unique id for radios
    if(form.querySelector("#wordInputs")) initQuestion("drag-drop", form);
    else if(form.querySelector("#mcqChoicesGrid")) initQuestion("mcq-text", form);
    else if(form.querySelector("#choicesGrid")) initQuestion("audio-image", form);
    else if(form.querySelector("#matchPairList")) initQuestion("match-image", form);
    else if(form.querySelector("#tfImageUpload")) initQuestion("true-false", form);
});
