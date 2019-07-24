function previewFile(elemName, elemPreview, elemNamePreview) {
    let preview = document.querySelector(elemPreview);
    let file    = document.querySelector('input[name=\''+elemName+'\']').files[0];
    let namePreview = document.querySelector(elemNamePreview);

    let reader  = new FileReader();

    reader.addEventListener("load", function () {
        // preview.src = reader.result; // TODO: uncomment to view image
    }, false);

    if (file) {
        namePreview.textContent = file.name;
        reader.readAsDataURL(file);
    }
}

function haveImage(elemName){
    let file    = document.querySelector('input[name=\''+elemName+'\']').files[0];
    if (!file) alert('¡Seleccione una imagen!')
    return !!file;
}