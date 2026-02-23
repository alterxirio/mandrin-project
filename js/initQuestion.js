function initQuestion(type, container){
    if(type==="drag-drop") DragDropInit(container);
    else if(type==="mcq-text") MCQInit(container);
    else if(type==="audio-image") AudioImageInit(container);
    else if(type==="match-image") MatchImageInit(container);
    else if(type==="true-false") TrueFalseInit(container);
}