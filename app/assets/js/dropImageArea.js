const dropArea = document.getElementById('drop-area');
const inputFile = document.getElementById('event-images');
const imageViewText = document.getElementById('image-view-text');
const imageRecap = document.getElementById('image-recap');

// Vérifier que les éléments existent
if (dropArea && inputFile && imageViewText && imageRecap) {
    // Stocker les fichiers sélectionnés
    let selectedFiles = [];

    inputFile.addEventListener("change", function (e) {
        uploadImage(e.target.files);
    });

    function displayRecapImage(file, index)
    {
        const reader = new FileReader();
        reader.onload = function (e) {
            let div = document.createElement('div');
            div.classList.add('img-recap');
            div.style.display = 'inline-block';
            div.style.margin = '5px';
            div.style.position = 'relative';

            let img = document.createElement("img");
            img.src = e.target.result;
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';

            // Bouton pour supprimer l'image
            let removeBtn = document.createElement('button');
            removeBtn.innerHTML = '×';
            removeBtn.type = 'button'; // IMPORTANT: empêche la soumission du formulaire
            removeBtn.style.position = 'absolute';
            removeBtn.style.top = '0';
            removeBtn.style.right = '0';
            removeBtn.style.background = 'red';
            removeBtn.style.color = 'white';
            removeBtn.style.border = 'none';
            removeBtn.style.cursor = 'pointer';
            removeBtn.style.width = '25px';
            removeBtn.style.height = '25px';
            removeBtn.onclick = function () {
                selectedFiles.splice(index, 1);
                updateFileInput();
                div.remove();
            };

            div.appendChild(img);
            div.appendChild(removeBtn);
            imageRecap.appendChild(div);
        };
        reader.readAsDataURL(file);
    }

    function uploadImage(files)
    {
        if (files && files.length > 0) {
            // Ajouter les nouveaux fichiers
            Array.from(files).forEach((file, index) => {
                selectedFiles.push(file);
                displayRecapImage(file, selectedFiles.length - 1);
            });

            // Mettre à jour l'affichage
            if (selectedFiles.length > 0) {
                imageViewText.style.display = "none";
                dropArea.style.border = '2px dashed #ccc';
            }

            updateFileInput();
        }
    }

    function updateFileInput()
    {
        // Créer un nouveau DataTransfer pour mettre à jour l'input
        const dataTransfer = new DataTransfer();
        selectedFiles.forEach(file => {
            dataTransfer.items.add(file);
        });
        inputFile.files = dataTransfer.files;
    }

    // Événements drag & drop
    dropArea.addEventListener("dragover", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.style.borderColor = '#4CAF50';
    });

    dropArea.addEventListener("dragleave", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.style.borderColor = '#ccc';
    });

    dropArea.addEventListener("drop", function (e) {
        e.preventDefault();
        e.stopPropagation();
        dropArea.style.borderColor = '#ccc';

        const files = e.dataTransfer.files;
        uploadImage(files);
    });

    // Click sur la zone pour ouvrir le sélecteur
    dropArea.addEventListener("click", function (e) {
        // Ne pas déclencher si on clique sur une image dans le recap
        if (!e.target.closest('.img-recap')) {
            inputFile.click();
        }
    });
}