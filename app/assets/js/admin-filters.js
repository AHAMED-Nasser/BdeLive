/**
 * Admin Panel Filters - AJAX Handling
 *
 * Handles dynamic updates of the user list without page reloads.
 * Intercepts clicks on filters, pagination, and search form submission.
 */

document.addEventListener('DOMContentLoaded', function () {
    const contentArea = document.getElementById('admin-content-area');

    if (!contentArea) {
        return;
    }

    // Fonction pour charger le contenu via AJAX
    async function loadContent(url, updateHistory = true)
    {
        try {
            // Ajouter un indicateur de chargement
            contentArea.style.opacity = '0.5';
            contentArea.style.pointerEvents = 'none';

            // Ajouter le paramètre ajax=1 à l'URL
            const fetchUrl = new URL(url, window.location.origin);
            fetchUrl.searchParams.set('ajax', '1');

            const response = await fetch(fetchUrl);

            if (!response.ok) {
                throw new Error('Erreur réseau');
            }

            const html = await response.text();

            // Mettre à jour le contenu
            contentArea.innerHTML = html;

            // Mettre à jour l'URL du navigateur
            if (updateHistory) {
                window.history.pushState({}, '', url);
            }

            // Mettre à jour les classes "active" dans la sidebar (utiliser l'URL originale)
            updateActiveFilters(new URL(url, window.location.origin));
        } catch (error) {
            console.error('Erreur lors du chargement:', error);
            // Fallback : recharger la page en cas d'erreur
            window.location.href = url;
        } finally {
            // Retirer l'indicateur de chargement
            contentArea.style.opacity = '1';
            contentArea.style.pointerEvents = 'auto';

            // Réattacher les écouteurs d'événements si nécessaire
            // (ici non nécessaire car on utilise la délégation via document ou des sélecteurs statiques hors de contentArea pour la navigation)
            // Mais pour la pagination à l'intérieur, c'est géré par la délégation ci-dessous.
        }
    }

    // Mettre à jour les classes actives dans la sidebar et les champs cachés du formulaire
    function updateActiveFilters(url)
    {
        const params = url.searchParams;
        const currentRole = params.get('role') || 'all';
        const currentFilter = params.get('filter') || 'active';
        const currentSearch = params.get('search') || '';

        // Mettre à jour les filtres de statut
        document.querySelectorAll('.admin-nav-list:not(.role-filters) .admin-nav-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkFilter = linkUrl.searchParams.get('filter');

            if (linkFilter === currentFilter) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Mettre à jour les filtres de rôle
        document.querySelectorAll('.role-filters .admin-nav-link').forEach(link => {
            const linkUrl = new URL(link.href, window.location.origin);
            const linkRole = linkUrl.searchParams.get('role');

            if (linkRole === currentRole) {
                link.classList.add('active');
            } else {
                link.classList.remove('active');
            }
        });

        // Mettre à jour les champs cachés du formulaire de recherche
        const searchForm = document.querySelector('.admin-search-form');
        if (searchForm) {
            const filterInput = searchForm.querySelector('input[name="filter"]');
            const roleInput = searchForm.querySelector('input[name="role"]');
            const searchInput = searchForm.querySelector('input[name="search"]');
            
            if (filterInput) {
                filterInput.value = currentFilter;
            }
            if (roleInput) {
                roleInput.value = currentRole;
            }
            if (searchInput) {
                searchInput.value = currentSearch;
            }
        }
    }

    // Délégation d'événements pour les liens de pagination (qui sont recréés dynamiquement)
    // et les liens de filtre (statiques)
    document.addEventListener('click', function (e) {
        // Liens de filtres et pagination
        const link = e.target.closest('.admin-nav-link, .pagination a, .admin-reset-btn');

        if (link && !e.ctrlKey && !e.metaKey && !e.shiftKey) {
            e.preventDefault();
            loadContent(link.href);
        }
    });

    // Intercepter la soumission du formulaire de recherche
    const searchForm = document.querySelector('.admin-search-form');
    if (searchForm) {
        searchForm.addEventListener('submit', function (e) {
            e.preventDefault();

            const formData = new FormData(searchForm);
            const params = new URLSearchParams(formData);

            // S'assurer que les paramètres de filtre sont préservés depuis l'URL actuelle
            const currentUrl = new URL(window.location.href);
            const currentFilter = currentUrl.searchParams.get('filter') || 'active';
            const currentRole = currentUrl.searchParams.get('role') || 'all';
            
            // Mettre à jour les paramètres avec les valeurs actuelles si elles ne sont pas dans le formulaire
            if (!params.has('filter') || params.get('filter') === '') {
                params.set('filter', currentFilter);
            }
            if (!params.has('role') || params.get('role') === '') {
                params.set('role', currentRole);
            }

            // Construire l'URL avec tous les paramètres
            const url = 'index.php?' + params.toString();

            loadContent(url);
        });
    }

    // Gérer le bouton Précédent/Suivant du navigateur
    window.addEventListener('popstate', function () {
        loadContent(window.location.href, false);
    });
});
