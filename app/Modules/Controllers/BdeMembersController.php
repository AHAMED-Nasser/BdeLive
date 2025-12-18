<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

/**
 * BdeMembersController - Affiche la liste des membres du BDE (sans photos),
 * réservée aux utilisateurs connectés.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BdeLive Team
 */
class BdeMembersController extends AuthenticatedController
{
    // Dans le constructeur de tes contrôleurs :
    public function __construct()
    {
        // parent::__construct($params); <-- ERREUR ICI
        parent::__construct(); // <-- SOLUTION : Enlever $params

        // Membres actifs du Bureau
        $bdeMembers = [
            ['firstname' => 'Ewan', 'lastname' => 'EL KIHAL', 'role' => 'Président', 'description' => 'Responsable de l\'équipe et de la vision globale.'],
            ['firstname' => 'Matteo', 'lastname' => 'BELZ', 'role' => 'Co-président', 'description' => 'Gestion de la communication et des réunions.'],
            ['firstname' => 'Nassim', 'lastname' => 'BUCHMULLER', 'role' => 'Secrétaire', 'description' => 'Responsable marketing et design.'],
            ['firstname' => 'Valentin', 'lastname' => 'GORGODIAN', 'role' => 'Trésorier', 'description' => 'Gestion du budget et des finances.'],
            ['firstname' => 'Claire', 'lastname' => 'ARSENA', 'role' => 'Responsable événements et communication', 'description' => 'Relations inter-associations et partenariats.'],
            ['firstname' => 'Pablo', 'lastname' => 'SENE', 'role' => 'Pôle événements', 'description' => 'Membre actif du pôle événementiel.'],
        ];

        // Membres d'honneur
        $honorMembers = [
            ['firstname' => 'Baptiste', 'lastname' => 'TURMO'],
            ['firstname' => 'Cyril', 'lastname' => 'TAMINE'],
            ['firstname' => 'Naël', 'lastname' => 'TURLURE'],
        ];

        // Affiche la nouvelle vue sans photos.
        $this->render('public/bdeMembersView', [
            'bdeMembers' => $bdeMembers,
            'honorMembers' => $honorMembers,
        ]);
    }
}
