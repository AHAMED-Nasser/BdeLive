<?php

declare(strict_types=1);

/**
 * Event Controller - Gestion des événements avec pagination
 * 
 * Rôle MVC : CONTROLLER (orchestration et logique métier)
 * 
 * Ce qu'il FAIT :
 * - Instancie le Repository (Model)
 * - Récupère le total depuis le Repository
 * - Crée l'objet Pagination (Helper)
 * - Récupère les données paginées depuis le Repository
 * - Passe les données à la View
 * 
 * Ce qu'il NE FAIT PAS :
 * - ❌ Requêtes SQL directes (c'est le Repository)
 * - ❌ Affichage HTML (c'est la View)
 * - ❌ Calculs pagination (c'est le Helper)
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 */
class EventController extends DefaultController
{
    private const ITEMS_PER_PAGE = 4; // Nombre d'événements par page

    /**
     * Constructeur - Initialise le contrôleur et charge la vue
     */
    public function __construct()
    {
        parent::__construct();
        $this->loadView();
    }

    /**
     * Charge la vue avec les données nécessaires
     * 
     * @param string $viewName Nom de la vue à charger (ignoré, utilise toujours eventView)
     */
    protected function loadView(string $viewName = 'eventView'): void
    {
        try {
            // 1. MODEL (Repository)
            $repository = new EventRepository();
            $totalEvents = $repository->count();

            // 2. HELPER (Pagination)
            $pagination = new Pagination($totalEvents, self::ITEMS_PER_PAGE);

            // 3. MODEL (Repository)
            $events = $repository->findPaginated(
                $pagination->getOffset(),
                $pagination->getLimit()
            );

            // 4. VIEW - Inclure la vue et lui passer les données
            // Ces variables ($events, $pagination) seront disponibles dans la vue
            require __DIR__ . '/../../views/events/eventView.php';

        } catch (Exception $e) {
            // Gérer les erreurs (par ex., afficher une page d'erreur)
            echo 'Erreur : ' . $e->getMessage();
        }
    }
}
