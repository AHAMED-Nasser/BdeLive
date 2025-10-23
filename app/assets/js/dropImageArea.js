const dropArea = document.getElementById('drop-area');
const inputFile = document.getElementById('event-images');
const imageViewText = document.getElementById('image-view-text');
const imageRecap = document.getElementById('image-recap');

// Recap image
let div = document.createElement('div');
div.classList.add('img-recap');

inputFile.addEventListener("change", uploadImage);

function displayRecapImage(imageLink) {
    // Create recap image
    let img = document.createElement("img");
    img.src = `${imageLink}`;
    div.appendChild(img);
    imageRecap.append(div);
}
function uploadImage() {
    let imageLink = URL.createObjectURL(inputFile.files[0]);
    dropArea.style.backgroundImage = `url(${imageLink}`;
    imageViewText.style.display = "none";
    dropArea.style.border = 0;

    displayRecapImage(imageLink);

}
dropArea.addEventListener("dragover", function (e) {
    e.preventDefault();
});

dropArea.addEventListener("drop", function (e) {
    e.preventDefault();
    inputFile.files = e.dataTransfer.files;
    uploadImage();
});

