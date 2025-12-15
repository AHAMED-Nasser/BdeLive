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
    /**
     * @param array<string, mixed>|null $params Les paramètres passés au contrôleur.
     */
    public function __construct(?array $params = null)
    {
        // Vérifie l'authentification.
        parent::__construct($params);

        // NOTE : Vous devez adapter la logique ci-dessous pour récupérer
        // VOS VRAIES DONNÉES des membres du BDE depuis la base de données.
        $bdeMembers = [
            [
                'firstname' => 'Cyril',
                'lastname' => 'Tamine',
                'role' => 'Président',
                'description' => 'Responsable de l\'équipe et de la vision globale.',
            ],
            [
                'firstname' => 'Romain',
                'lastname' => 'DUPONT',
                'role' => 'Secrétaire',
                'description' => 'Gestion de la communication et des réunions.',
            ],
            [
                'firstname' => 'Thomas',
                'lastname' => 'MARTIN',
                'role' => 'Trésorier',
                'description' => 'Gestion du budget et des finances.',
            ],
        ];

        // Affiche la nouvelle vue sans photos.
        $this->render('public/bdeMembersView', [
            'bdeMembers' => $bdeMembers,
        ]);
    }
}