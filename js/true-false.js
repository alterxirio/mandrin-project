function TrueFalseInit(container){
    const imageUpload = container.querySelector("#tfImageUpload");
    const imagePreview = container.querySelector("#tfImagePreview");
    if(!imageUpload || !imagePreview) return;

    imageUpload.addEventListener("change",()=>{
        const file=imageUpload.files[0];
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
}