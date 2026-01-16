<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

/**
 * BdeMembersController - Displays the list of BDE members (without photos),
 * reserved for authenticated users.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BdeLive - Group 8
 */
class BdeMembersController extends AuthenticatedController
{
    /**
     * Constructor - Initializes the controller and renders the BDE members view
     */
    public function __construct()
    {
        parent::__construct();

        // Active BDE board members
        $bdeMembers = [
            [
                'firstname' => 'Ewan',
                'lastname' => 'EL KIHAL',
                'role' => 'Président',
                'description' => 'Chef d\'équipe et vision globale.'
            ],
            [
                'firstname' => 'Matteo',
                'lastname' => 'BELZ',
                'role' => 'Co-président',
                'description' => 'Communication et gestion des réunions.'
            ],
            [
                'firstname' => 'Jules',
                'lastname' => 'RIBBE',
                'role' => 'Vice-président, responsable logistique',
                'description' => 'Coordination logistique et support opérationnel.'
            ],
            [
                'firstname' => 'Valentin',
                'lastname' => 'GORGODIAN',
                'role' => 'Trésorier',
                'description' => 'Gestion du budget et des finances.'
            ],
            [
                'firstname' => 'Nassim',
                'lastname' => 'BUCHMULLER',
                'role' => 'Secrétaire, responsable marketing et design',
                'description' => 'Gestion administrative, marketing et identité visuelle.'
            ],
            [
                'firstname' => 'Claire',
                'lastname' => 'ARSENA',
                'role' => 'Responsable événements et communication, relations inter-associations, partenariats/sponsoring',
                'description' => 'Organisation des événements, communication externe et développement des partenariats.'
            ],
            [
                'firstname' => 'Pablo',
                'lastname' => 'SENE',
                'role' => 'Pôle événements',
                'description' => 'Membre actif du pôle événementiel.'
            ],
        ];

        // Honorary members
        $honorMembers = [
            ['firstname' => 'Baptiste', 'lastname' => 'TURMO'],
            ['firstname' => 'Cyril', 'lastname' => 'TAMINE'],
            ['firstname' => 'Naël', 'lastname' => 'TURLURE'],
        ];

        // Render the new view without photos.
        $this->render('public/bdeMembersView', [
            'bdeMembers' => $bdeMembers,
            'honorMembers' => $honorMembers,
        ]);
    }
}
