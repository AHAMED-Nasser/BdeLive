<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\AuthenticatedController;

/**
 * AboutController - Affiche la page d'information "À propos" pour les membres.
 *
 * Hérite d'AuthenticatedController pour s'assurer que seuls les membres connectés
 * peuvent y accéder. Les utilisateurs non connectés seront redirigés.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 */
class AboutController extends AuthenticatedController
{

    public function __construct()
    {
        // La méthode parent vérifie l'authentification et redirige si l'utilisateur n'est pas connecté.
        parent::__construct();

        // Charge la vue de la page "À propos" sans les photos
        $this->render('public/aboutView');
    }
}
