<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * ScheduleController - Gestion des emplois du temps
 *
 * Affiche les emplois du temps par groupe avec FullCalendar
 *
 * @package App\Modules\Controllers
 */
class ScheduleController extends DefaultController
{
    private const ICS_DIRECTORY = __DIR__ . '/../../../Schedule/';

    // Définition des groupes (uniquement demi-groupes)
    private const GROUPS = [
        '1ere' => [
            'name' => '1ère année',
            'groups' => [
                'G1A' => 'Groupe 1A',
                'G1B' => 'Groupe 1B',
                'G2A' => 'Groupe 2A',
                'G2B' => 'Groupe 2B',
                'G3A' => 'Groupe 3A',
                'G3B' => 'Groupe 3B',
                'G4A' => 'Groupe 4A',
                'G4B' => 'Groupe 4B'
            ]
        ],
        '2eme' => [
            'name' => '2ème année',
            'groups' => [
                'GA1-1' => 'Groupe A1-1',
                'GA1-2' => 'Groupe A1-2',
                'GA2-1' => 'Groupe A2-1',
                'GA2-2' => 'Groupe A2-2',
                'GB-1' => 'Groupe B-1',
                'GB-2' => 'Groupe B-2',
            ]
        ],
        '3eme' => [
            'name' => '3ème année',
            'groups' => [
                'GA1-1' => 'Groupe A1-1',
                'GA1-2' => 'Groupe A1-2',
                'GA2-1' => 'Groupe A2-1',
                'GA2-2' => 'Groupe A2-2',
                'GB-1' => 'Groupe B-1',
                'GB-2' => 'Groupe B-2',
            ]
        ]
    ];

    public function __construct()
    {
        parent::__construct();

        // Si c'est une requête API pour les événements
        if ($this->request->get('action') === 'get-events') {
            $this->getEvents();
            exit;
        }

        // Afficher la page principale
        $this->showSchedulePage();
    }

    /**
     * Affiche la page de sélection et visualisation des emplois du temps
     */
    private function showSchedulePage(): void
    {
        $selectedYear = $this->request->get('year', '1ere');
        $selectedGroup = $this->request->get('group', '');

        // Valider l'année sélectionnée
        if (!array_key_exists($selectedYear, self::GROUPS)) {
            $selectedYear = '1ere';
        }

        // Valider le groupe sélectionné
        if ($selectedGroup && !array_key_exists($selectedGroup, self::GROUPS[$selectedYear]['groups'])) {
            $selectedGroup = '';
        }

        $this->render('public/scheduleView', [
            'groups' => self::GROUPS,
            'selectedYear' => $selectedYear,
            'selectedGroup' => $selectedGroup,
        ]);
    }

    /**
     * API : Retourne les événements en JSON pour FullCalendar
     */
    private function getEvents(): void
    {
        $year = $this->request->get('year', '1ere');
        $group = $this->request->get('group', '');

        if (!$group || !array_key_exists($year, self::GROUPS)) {
            $this->response->json(['error' => 'Paramètres invalides'], 400);
        }

        // Mapper l'année vers le fichier .ics correspondant
        $icsFiles = [
            '1ere' => 'ADE1ereAnnee.ics',
            '2eme' => 'ADE2emeAnnee.ics',
            '3eme' => 'ADE3emeAnnee.ics',
        ];

        $icsFile = self::ICS_DIRECTORY . ($icsFiles[$year]);

        if (!file_exists($icsFile)) {
            $this->response->json(['error' => 'Fichier emploi du temps introuvable'], 404);
        }

        $events = $this->parseIcsFile($icsFile, $group, $year);
        $this->response->json($events);
    }

    /**
     * Parse un fichier .ics et retourne les événements filtrés par groupe
     *
     * @param string $filePath Chemin du fichier .ics
     * @param string $group Groupe à filtrer (ex: 'G1A')
     * @param string $year Année sélectionnée (ex: '1ere')
     * @return array<int, array<string, mixed>> Événements au format FullCalendar
     */
    private function parseIcsFile(string $filePath, string $group, string $year): array
    {
        $content = file_get_contents($filePath);
        if ($content === false) {
            return [];
        }

        // Normaliser les fins de ligne et supprimer le "line folding" (repli de ligne ADE)
        $content = preg_replace('/\r\n\s+/', '', $content); // Rejoint les lignes coupées
        $lines = preg_split('/\r\n|\r|\n/', $content ?? '') ?: []; // Découpe proprement

        $events = [];
        $currentEvent = null;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            if ($line === 'BEGIN:VEVENT') {
                $currentEvent = [];
            } elseif ($line === 'END:VEVENT' && $currentEvent !== null) {
                if ($this->eventMatchesGroup($currentEvent, $group, $year)) {
                    $events[] = $this->formatEventForFullCalendar($currentEvent);
                }
                $currentEvent = null;
            } elseif ($currentEvent !== null && str_contains($line, ':')) {
                // Utilisation de preg_split pour éviter les erreurs sur les URL ou descriptions complexes
                $parts = preg_split('/(?<!\\\\):/', $line, 2) ?: [];
                if (count($parts) === 2) {
                    $key = $parts[0];
                    $value = str_replace(['\,', '\;'], [',', ';'], $parts[1]);
                    $currentEvent[$key] = $value;
                }
            }
        }
        return $events;
    }

    /**
     * Vérifie si un événement concerne le demi-groupe spécifié
     *
     * Pour un demi-groupe sélectionné (ex: "G1A"), affiche :
     * - Les cours spécifiques au demi-groupe : "G1A"
     * - Les cours du groupe entier : "G1", "Groupe 1"
     *
     * @param array<string, string> $event Événement .ics
     * @param string $group Demi-groupe sélectionné (ex: "G1A")
     * @param string $year Année sélectionnée (ex: "1ere") - non utilisé
     * @return bool True si l'événement concerne ce demi-groupe
     */
    private function eventMatchesGroup(array $event, string $group, string $year): bool
    {
        $summary = $event['SUMMARY'] ?? '';
        $description = $event['DESCRIPTION'] ?? '';
        $content = $summary . ' ' . $description;

        // Vérifier si l'événement concerne l'année entière (Promotion)
        // On cherche "1ère année", "2ème année", etc.
        $yearLabel = self::GROUPS[$year]['name']; // Récupère "1ère année", etc.
        $isPromotionEvent = stripos($content, $yearLabel) !== false ||
            stripos($content, '1ere annee') !== false ||
            stripos($content, '(INFO)') !== false;

        if ($isPromotionEvent) {
            return true;
        }

        // Cas particuliers : Mention "INFO" ou "1ere annee" sans accent
        if (stripos($content, $group) !== false) {
            return true;
        }

        // Format 1ère année (ex: G1A)
        if (preg_match('/^G(\d+)([AB])$/', $group, $matches)) {
            $groupNum = $matches[1];
            $groupLetter = $matches[2];
            $parentGroup = 'G' . $groupNum;
            $otherHalfGroup = $parentGroup . ($groupLetter === 'A' ? 'B' : 'A');

            // Vérifier le groupe parent (G1) sans que ce soit spécifiquement l'autre demi-groupe
            $pattern = '/\b' . preg_quote($parentGroup, '/') . '\b(?![AB\d\-])/i';
            if (preg_match($pattern, $content) && stripos($content, $otherHalfGroup) === false) {
                return true;
            }
        } elseif (preg_match('/^(G[A-B]\d?)-(\d)$/', $group, $matches)) { //Format 2ème/3ème année (ex: GA1-1 ou GB-2)
            $parentGroup = $matches[1]; // ex: "GA1" ou "GB"
            $subNum = $matches[2];      // ex: "1" ou "2"
            $otherSub = ($subNum === '1' ? '2' : '1');
            $otherHalfGroup = $parentGroup . '-' . $otherSub;

            // Si le texte contient le groupe parent (ex : "GA1") mais pas spécifiquement l'autre sous-groupe
            if (stripos($content, $parentGroup) !== false && stripos($content, $otherHalfGroup) === false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Formate un événement .ics pour FullCalendar
     * @param array<string,string> $event Événement .ics (clé/valeur)
     * @return array<string, mixed> Événement formaté pour FullCalendar
     */
    private function formatEventForFullCalendar(array $event): array
    {
        $start = $this->parseIcsDate($event['DTSTART'] ?? '');
        $end = $this->parseIcsDate($event['DTEND'] ?? '');
        $summary = $this->cleanIcsText($event['SUMMARY'] ?? 'Sans titre');
        $location = $this->cleanIcsText($event['LOCATION'] ?? '');
        $description = $this->cleanIcsText($event['DESCRIPTION'] ?? '');

        // Extraire le prof de la description si présent
        $teacher = '';
        if (preg_match('/([A-Z\s]+)\s*\n/i', $description, $matches)) {
            $teacher = trim($matches[1]);
        }

        // Déterminer la couleur selon le type de cours
        $color = $this->getEventColor($summary);

        return [
            'title' => $summary,
            'start' => $start,
            'end' => $end,
            'location' => $location,
            'teacher' => $teacher,
            'description' => $description,
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'location' => $location,
                'teacher' => $teacher,
            ]
        ];
    }

    /**
     * Parse une date au format .ics (YYYYMMDDTHHMMSSZ)
     */
    private function parseIcsDate(string $icsDate): string
    {
        if (strlen($icsDate) < 15) {
            return date('Y-m-d\TH:i:s');
        }

        // Format: 20251215T123000Z
        $year = substr($icsDate, 0, 4);
        $month = substr($icsDate, 4, 2);
        $day = substr($icsDate, 6, 2);
        $hour = substr($icsDate, 9, 2);
        $minute = substr($icsDate, 11, 2);

        return "$year-$month-{$day}T$hour:$minute:00";
    }

    /**
     * Nettoie le texte extrait d'un fichier .ics
     */
    private function cleanIcsText(string $text): string
    {
        // Supprimer les retours à la ligne inutiles
        $text = str_replace(['\n', '\r\n', '\\n'], ' ', $text);
        // Supprimer les espaces multiples
        $text = preg_replace('/\s+/', ' ', $text);
        return trim($text ?? '');
    }

    /**
     * Retourne une couleur selon le type de cours
     */
    private function getEventColor(string $summary): string
    {
        if (stripos($summary, 'TD') !== false) {
            return '#3788d8'; // Bleu
        }
        if (stripos($summary, 'TP') !== false) {
            return '#28a745'; // Vert
        }
        if (stripos($summary, 'CM') !== false || stripos($summary, 'Cours') !== false) {
            return '#dc3545'; // Rouge
        }
        if (stripos($summary, 'Examen') !== false || stripos($summary, 'Test') !== false) {
            return '#fd7e14'; // Orange
        }
        if (stripos($summary, 'Soutenance') !== false) {
            return '#6f42c1'; // Violet
        }
        if (stripos($summary, 'Support') !== false || stripos($summary, 'autonomie') !== false) {
            return '#17a2b8'; // Cyan
        }
        return '#6c757d'; // Gris par défaut
    }
}
