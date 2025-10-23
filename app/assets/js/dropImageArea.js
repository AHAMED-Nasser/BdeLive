const dropArea = document.getElementById('drop-area');
const inputFile = document.getElementById('event-images');

inputFile.addEventListener("change", uploadImage);

function uploadImage() {
    let imageLink = URL.createObjectURL(inputFile.files[0]);
    dropArea.style.backgroundImage = `url(${imageLink}`;
    dropArea.textContent = "";
    dropArea.style.border = 0;
}

dropArea.addEventListener("dragover", function (e) {
    e.preventDefault();
});

dropArea.addEventListener("drop", function (e) {
    e.preventDefault();
    inputFile.files = e.dataTransfer.files;
    uploadImage();
});