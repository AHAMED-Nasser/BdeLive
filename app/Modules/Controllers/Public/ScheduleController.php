<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Public;

use App\Modules\Controllers\DefaultController;

/**
 * Controller responsible for managing schedules.
 * Displays group schedules using FullCalendar.
 *
 * @author BDELIVE - Group 8
 * @package App\Modules\Controllers\Public
 * @version 1.0.0
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

    /**
     * Initializes the controller.
     * Handles API requests for events and AJAX requests for views.
     * Starts the session and sets up the environment.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        // Si c'est une requête API pour les événements
        if ($this->request->get('action') === 'get-events') {
            $this->getEvents();
            exit;
        }

        // Si c'est une requête AJAX pour charger une vue
        if ($this->request->get('action') === 'load-view') {
            $this->loadViewAjax();
            exit;
        }

        // Afficher la page principale
        $this->showSchedulePage();
    }

    /**
     * Loads a specific view via AJAX (returns only the HTML of the view).
     *
     * @return void
     */
    private function loadViewAjax(): void
    {
        header('Content-Type: application/json');

        $selectedYear = $this->request->get('year', '1ere');
        $selectedGroup = $this->request->get('group', '');
        $view = $this->request->get('view', 'week');

        if (!in_array($view, ['day', 'week', 'month'])) {
            echo json_encode(['error' => 'Vue invalide']);
            exit;
        }

        if (!$selectedGroup) {
            echo json_encode(['error' => 'Groupe non sélectionné']);
            exit;
        }

        // Paramètres temporels selon la vue
        $week = (int) ($this->request->get('week') ?? date('W'));
        $dateParam = $this->request->get('date');
        // S'assurer que $date est une string
        $date = is_array($dateParam) ? ($dateParam[0] ?? date('Y-m-d')) : ($dateParam ?? date('Y-m-d'));
        $calYear = (int) ($this->request->get('calyear') ?? date('Y'));
        $calMonth = (int) ($this->request->get('calmonth') ?? date('n'));

        $extraParams = [
            'year' => $selectedYear,
            'group' => $selectedGroup
        ];
        $pageUrl = 'index.php?page=schedule';

        // Générer la vue demandée
        ob_start();

        try {
            if ($view === 'day') {
                $events = $this->getEventsForDay($selectedYear, $selectedGroup, $date);
                $dayManager = new \App\Core\DayScheduleManager();
                $daySchedule = $dayManager->generateDaySchedule($date, $events);
                // Calculer la période pour l'affichage (s'assurer que $date est une string)
                if (is_string($date)) {
                    $timestamp = strtotime($date);
                    $weekPeriod = ($timestamp !== false) ? date('d/m', $timestamp) : date('d/m');
                } else {
                    $weekPeriod = date('d/m');
                }
                // Passer la variable $view au template
                include __DIR__ . '/../../views/components/day-schedule.php';
            } elseif ($view === 'month') {
                $events = $this->getEventsForNativeCalendar($selectedYear, $selectedGroup, $calMonth, $calYear);
                $calendarManager = new \App\Core\CalendarManager();
                $calendar = $calendarManager->generateMonthCalendar($calYear, $calMonth, $events);
                // Passer la variable $view au template
                include __DIR__ . '/../../views/components/calendar.php';
            } else {
                // Vue semaine
                $weekEvents = $this->getEventsForWeeklySchedule($selectedYear, $selectedGroup, $week, $calYear);
                $weeklyManager = new \App\Core\WeeklyScheduleManager();
                $schedule = $weeklyManager->generateWeeklySchedule($calYear, $week, $weekEvents);
                // Calculer la période pour l'affichage
                $weekPeriod = '';
                if (!empty($schedule['weekDates'])) {
                    $firstDate = reset($schedule['weekDates']);
                    $lastDate = end($schedule['weekDates']);
                    $weekPeriod = $firstDate['formatted'] . ' - ' . $lastDate['formatted'];
                }
                // Passer la variable $view au template
                include __DIR__ . '/../../views/components/weekly-schedule.php';
            }

            $html = ob_get_clean();

            if (empty($html)) {
                throw new \Exception('Le template n\'a généré aucun contenu');
            }

            echo json_encode([
                'success' => true,
                'html' => $html,
                'view' => $view
            ]);
        } catch (\Throwable $e) {
            ob_end_clean();
            echo json_encode([
                'success' => false,
                'error' => 'Erreur lors du chargement: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Displays the main schedule selection and visualization page.
     *
     * @return void
     */
    private function showSchedulePage(): void
    {
        $selectedYear = $this->request->get('year', '1ere');
        $selectedGroup = $this->request->get('group', '');

        // Nouveau : détection du type de calendrier (FullCalendar vs Native)
        $calendarType = $this->request->get('calendar', 'fullcalendar'); // 'native' ou 'fullcalendar'

        // Paramètres pour le calendrier natif (IMPORTANT: "calmonth" pour éviter confusion avec year du groupe)
        $month = (int) ($this->request->get('calmonth') ?? date('n'));
        $calYear = (int) ($this->request->get('calyear') ?? date('Y'));

        // Validation des paramètres du calendrier natif
        if ($month < 1 || $month > 12) {
            $month = (int) date('n');
        }

        // Valider l'année sélectionnée (groupe)
        if (!array_key_exists($selectedYear, self::GROUPS)) {
            $selectedYear = '1ere';
        }

        // Valider le groupe sélectionné
        if ($selectedGroup && !array_key_exists($selectedGroup, self::GROUPS[$selectedYear]['groups'])) {
            $selectedGroup = '';
        }

        // Gestion des vues (jour, semaine, mois)
        $view = $this->request->get('view', 'week');
        if (!in_array($view, ['day', 'week', 'month'])) {
            $view = 'week';
        }

        // Variables pour les vues
        $weeklySchedule = null;
        $daySchedule = null;
        $monthCalendar = null;

        // Paramètres temporels
        $week = (int) ($this->request->get('week') ?? date('W'));
        if ($week < 1 || $week > 53) {
            $week = (int) date('W');
        }

        $date = $this->request->get('date') ?? date('Y-m-d');

        if ($selectedGroup) {
            if ($view === 'day') {
                // VUE JOUR
                $events = $this->getEventsForDay($selectedYear, $selectedGroup, $date);
                $dayManager = new \App\Core\DayScheduleManager();
                $daySchedule = $dayManager->generateDaySchedule($date, $events);
            } elseif ($view === 'month') {
                // VUE MOIS
                // Utilise calmonth/calyear ou le mois courant
                $targetMonth = (int) ($this->request->get('calmonth') ?? date('n'));
                $targetYear = (int) ($this->request->get('calyear') ?? date('Y'));

                $events = $this->getEventsForNativeCalendar($selectedYear, $selectedGroup, $targetMonth, $targetYear);
                $calendarManager = new \App\Core\CalendarManager();
                $monthCalendar = $calendarManager->generateMonthCalendar($targetYear, $targetMonth, $events);
            } else {
                // VUE SEMAINE (Défaut)
                $weekEvents = $this->getEventsForWeeklySchedule($selectedYear, $selectedGroup, $week, $calYear);
                $weeklyManager = new \App\Core\WeeklyScheduleManager();
                $weeklySchedule = $weeklyManager->generateWeeklySchedule($calYear, $week, $weekEvents);
            }
        }

        $this->render('public/scheduleView', [
            'groups' => self::GROUPS,
            'selectedYear' => $selectedYear,
            'selectedGroup' => $selectedGroup,
            'view' => $view,
            'weeklySchedule' => $weeklySchedule,
            'daySchedule' => $daySchedule,
            'nativeCalendar' => $monthCalendar, // On réutilise la variable existante pour la vue mois
            'calMonth' => $month,
            'calYear' => $calYear,
            'week' => $week,
            'currentDate' => $date
        ]);
    }

    /**
     * Retrieves events for a specific day.
     *
     * @param string $yearLevel Year level (e.g., '1ere', '2eme')
     * @param string $group Group identifier
     * @param string $date Date string (YYYY-MM-DD)
     * @return array<int, array<string, mixed>> List of events for the day
     */
    private function getEventsForDay(string $yearLevel, string $group, string $date): array
    {
        // On récupère tous les événements du mois car le parsing ICS est optimisé par mois
        // Puis on filtre pour le jour spécifique
        $dateObj = new \DateTimeImmutable($date);
        $month = (int) $dateObj->format('n');
        $year = (int) $dateObj->format('Y');

        $monthEvents = $this->getEventsForNativeCalendar($yearLevel, $group, $month, $year);

        $dayEvents = [];
        foreach ($monthEvents as $event) {
            $eventStart = isset($event['start']) ? substr($event['start'], 0, 10) : '';
            if ($eventStart === $date) {
                $dayEvents[] = $event;
            }
        }

        return $dayEvents;
    }

    /**
     * API: Returns events in JSON format for FullCalendar.
     *
     * @return void
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
     * Parses an .ics file and returns events filtered by group.
     *
     * @param string $filePath Path to the .ics file
     * @param string $group Group to filter (e.g., 'G1A')
     * @param string $year Selected year (e.g., '1ere')
     * @return array<int, array<string, mixed>> Events formatted for FullCalendar
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
     * Checks if an event belongs to the specified half-group.
     *
     * For a selected half-group (e.g., "G1A"), matches:
     * - Specific courses for the half-group: "G1A"
     * - Whole group courses: "G1", "Groupe 1"
     *
     * @param array<string, string> $event .ics event data
     * @param string $group Selected half-group (e.g., "G1A")
     * @param string $year Selected year (e.g., "1ere")
     * @return bool True if the event matches the group
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
        } elseif (preg_match('/^(G[A-B]\d?)-(\d)$/', $group, $matches)) {
            //Format 2ème/3ème année (ex: GA1-1 ou GB-2)
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
     * Formats an .ics event for FullCalendar.
     *
     * @param array<string,string> $event .ics event data (key/value)
     * @return array<string, mixed> Event formatted for FullCalendar
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
     * Parses a date in .ics format (YYYYMMDDTHHMMSSZ).
     *
     * @param string $icsDate Date string in .ics format
     * @return string Formatted date string (YYYY-MM-DDTHH:MM:SS)
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
     * Cleans text extracted from an .ics file.
     *
     * @param string $text Raw text from .ics
     * @return string Cleaned text
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
     * Returns a color based on the course type.
     *
     * @param string $summary Event summary/title
     * @return string Hex color code
     */
    private function getEventColor(string $summary): string
    {
        // Couleurs optimisées pour un meilleur contraste avec le texte blanc (WCAG AA)
        if (stripos($summary, 'TD') !== false) {
            return '#2563eb'; // Bleu plus foncé (contraste amélioré)
        }
        if (stripos($summary, 'TP') !== false) {
            return '#15803d'; // Vert plus foncé (contraste amélioré)
        }
        if (stripos($summary, 'CM') !== false || stripos($summary, 'Cours') !== false) {
            return '#b91c1c'; // Rouge plus foncé (contraste amélioré)
        }
        if (stripos($summary, 'Examen') !== false || stripos($summary, 'Test') !== false) {
            return '#d97706'; // Orange plus foncé (contraste amélioré)
        }
        if (stripos($summary, 'Soutenance') !== false) {
            return '#5b21b6'; // Violet plus foncé (contraste amélioré)
        }
        if (stripos($summary, 'Support') !== false || stripos($summary, 'autonomie') !== false) {
            return '#0e7490'; // Cyan plus foncé (contraste amélioré)
        }
        return '#6c757d'; // Gris par défaut
    }

    /**
     * Retrieves events for the native calendar (filtered by month).
     *
     * @param string $year Year group ('1ere', '2eme', '3eme')
     * @param string $group Selected group
     * @param int $month Month (1-12)
     * @param int $calYear Calendar year (e.g., 2026)
     * @return array<int, array<string, mixed>> Events formatted for native calendar
     */
    private function getEventsForNativeCalendar(string $year, string $group, int $month, int $calYear): array
    {
        // Mapper l'année vers le fichier .ics correspondant
        $icsFiles = [
            '1ere' => 'ADE1ereAnnee.ics',
            '2eme' => 'ADE2emeAnnee.ics',
            '3eme' => 'ADE3emeAnnee.ics',
        ];

        $icsFile = self::ICS_DIRECTORY . ($icsFiles[$year] ?? '');

        if (!file_exists($icsFile)) {
            return [];
        }

        // Parser tous les événements
        $allEvents = $this->parseIcsFile($icsFile, $group, $year);

        // Filtrer par mois
        $monthStart = sprintf('%04d-%02d-01', $calYear, $month);
        $lastDay = cal_days_in_month(CAL_GREGORIAN, $month, $calYear);
        $monthEnd = sprintf('%04d-%02d-%02d', $calYear, $month, $lastDay);

        $filteredEvents = [];
        foreach ($allEvents as $event) {
            $eventDate = substr($event['start'] ?? '', 0, 10); // Extract YYYY-MM-DD

            if ($eventDate >= $monthStart && $eventDate <= $monthEnd) {
                // Adapter le format pour le calendrier natif
                $filteredEvents[] = [
                    'id' => md5($event['start'] . $event['title']),
                    'title' => $event['title'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'color' => $event['backgroundColor'],
                    'location' => $event['location'] ?? '',
                    'teacher' => $event['teacher'] ?? '',
                    'type' => $this->getEventType($event['title']),
                ];
            }
        }

        return $filteredEvents;
    }

    /**
     * Determines the event type from the title.
     *
     * @param string $title Event title
     * @return string Event type
     */
    private function getEventType(string $title): string
    {
        if (stripos($title, 'TD') !== false) {
            return 'td';
        }
        if (stripos($title, 'TP') !== false) {
            return 'tp';
        }
        if (stripos($title, 'CM') !== false) {
            return 'cm';
        }
        if (stripos($title, 'Examen') !== false) {
            return 'exam';
        }
        if (stripos($title, 'Soutenance') !== false) {
            return 'soutenance';
        }
        if (stripos($title, 'Support') !== false || stripos($title, 'autonomie') !== false) {
            return 'support';
        }
        return 'default';
    }

    /**
     * Retrieves events for the weekly view (filtered by week).
     *
     * @param string $year Year group ('1ere', '2eme', '3eme')
     * @param string $group Selected group
     * @param int $week Week number (1-53)
     * @param int $calYear Calendar year (e.g. 2026)
     * @return array<int, array<string, mixed>> Events formatted for weekly view
     */
    private function getEventsForWeeklySchedule(string $year, string $group, int $week, int $calYear): array
    {
        // Mapper l'année vers le fichier .ics correspondant
        $icsFiles = [
            '1ere' => 'ADE1ereAnnee.ics',
            '2eme' => 'ADE2emeAnnee.ics',
            '3eme' => 'ADE3emeAnnee.ics',
        ];

        $icsFile = self::ICS_DIRECTORY . ($icsFiles[$year] ?? '');

        if (!file_exists($icsFile)) {
            return [];
        }

        // Parser tous les événements
        $allEvents = $this->parseIcsFile($icsFile, $group, $year);

        // Utiliser WeeklyScheduleManager pour obtenir les dates de la semaine
        $weeklyManager = new \App\Core\WeeklyScheduleManager();
        $weekDates = $weeklyManager->generateWeeklySchedule($calYear, $week, [])['weekDates'];

        // Créer un array des dates de la semaine pour filtre rapide
        $weekDateStrings = [];
        foreach ($weekDates as $dayInfo) {
            $weekDateStrings[] = $dayInfo['date'];
        }

        // Filtrer les événements par semaine
        $filteredEvents = [];
        foreach ($allEvents as $event) {
            $eventDate = substr($event['start'] ?? '', 0, 10); // Extract YYYY-MM-DD

            if (in_array($eventDate, $weekDateStrings, true)) {
                // Adapter le format pour la vue hebdomadaire
                $filteredEvents[] = [
                    'id' => md5($event['start'] . $event['title']),
                    'title' => $event['title'],
                    'start' => $event['start'],
                    'end' => $event['end'],
                    'color' => $event['backgroundColor'],
                    'location' => $event['location'] ?? '',
                    'teacher' => $event['teacher'] ?? '',
                    'type' => $this->getEventType($event['title']),
                ];
            }
        }

        return $filteredEvents;
    }
}
