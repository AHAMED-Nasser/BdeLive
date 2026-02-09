// Fonction pour initialiser le drop area pour un formulaire spécifique
function initDropImageArea(inputId, maxFiles = null)
{
    const dropArea = document.getElementById('drop-area');
    const inputFile = document.getElementById(inputId);
    const imageViewText = document.getElementById('image-view-text');
    const imageRecap = document.getElementById('image-recap');

    // Vérifier que les éléments existent
    if (!dropArea || !inputFile || !imageViewText || !imageRecap) {
        return;
    }
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

            let img = document.createElement("img");
            img.src = e.target.result;
            img.alt = file.name || 'Image preview';

            // Bouton pour supprimer l'image
            let removeBtn = document.createElement('button');
            removeBtn.innerHTML = '<i class="fas fa-times" aria-hidden="true"></i>';
            removeBtn.type = 'button'; // IMPORTANT: empêche la soumission du formulaire
            removeBtn.setAttribute('aria-label', 'Supprimer cette image');
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
            // Si maxFiles est défini (ex: 1 pour les articles), limiter le nombre
            if (maxFiles !== null) {
                // Si on a déjà atteint la limite, vider d'abord
                if (selectedFiles.length >= maxFiles) {
                    selectedFiles = [];
                    // Supprimer toutes les images affichées
                    const existingImages = imageRecap.querySelectorAll('.img-recap');
                    existingImages.forEach(imgDiv => imgDiv.remove());
                }
                // Ne prendre que le premier fichier si limite à 1
                const filesToAdd = maxFiles === 1 ? [files[0]] : Array.from(files).slice(0, maxFiles - selectedFiles.length);
                filesToAdd.forEach((file) => {
                    selectedFiles.push(file);
                    displayRecapImage(file, selectedFiles.length - 1);
                });
            } else {
                // Ajouter les nouveaux fichiers (pas de limite)
                Array.from(files).forEach((file, index) => {
                    selectedFiles.push(file);
                    displayRecapImage(file, selectedFiles.length - 1);
                });
            }

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

// Initialiser pour la création d'événement (multiple images)
if (document.getElementById('event-images')) {
    initDropImageArea('event-images');
}

// Initialiser pour la modification d'événement (id event_images)
if (document.getElementById('event_images')) {
    initDropImageArea('event_images');
}

// Initialiser pour les articles (une seule image)
if (document.getElementById('article-image')) {
    initDropImageArea('article-image', 1);
}