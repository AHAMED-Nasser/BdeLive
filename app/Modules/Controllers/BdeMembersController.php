<?php

declare(strict_types=1);

namespace App\Modules\Controllers;

/**
 * BdeMembersController - Displays the list of BDE members (without photos),
 * reserved for authenticated users.
 *
 * @package BdeLive\Controllers
 * @version 1.0.0
 * @author BdeLive Team
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
                'role' => 'President',
                'description' => 'Team leader and overall vision.'
            ],
            [
                'firstname' => 'Matteo',
                'lastname' => 'BELZ',
                'role' => 'Co-President',
                'description' => 'Communication and meeting management.'
            ],
            [
                'firstname' => 'Nassim',
                'lastname' => 'BUCHMULLER',
                'role' => 'Secretary',
                'description' => 'Marketing and design manager.'
            ],
            [
                'firstname' => 'Valentin',
                'lastname' => 'GORGODIAN',
                'role' => 'Treasurer',
                'description' => 'Budget and finance management.'
            ],
            [
                'firstname' => 'Claire',
                'lastname' => 'ARSENA',
                'role' => 'Events and Communication Manager',
                'description' => 'Inter-association relations and partnerships.'
            ],
            [
                'firstname' => 'Pablo',
                'lastname' => 'SENE',
                'role' => 'Events Team',
                'description' => 'Active member of the events team.'
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
