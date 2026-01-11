<?php

namespace App\Core;

/**
 * CalendarManager - Gestionnaire de calendrier natif
 * 
 * Génère des calendriers mensuels au format CSS Grid sans dépendances externes.
 * Remplace FullCalendar pour une meilleure accessibilité et performance.
 * 
 * @package App\Core
 */
class CalendarManager
{
    /**
     * Noms des mois en français
     */
    private const MONTH_NAMES = [
        1 => 'Janvier', 2 => 'Février', 3 => 'Mars', 4 => 'Avril',
        5 => 'Mai', 6 => 'Juin', 7 => 'Juillet', 8 => 'Août',
        9 => 'Septembre', 10 => 'Octobre', 11 => 'Novembre', 12 => 'Décembre'
    ];

    /**
     * Noms des jours de la semaine (Lundi = 1, Dimanche = 7)
     */
    private const WEEKDAY_NAMES = [
        1 => 'Lundi', 2 => 'Mardi', 3 => 'Mercredi', 4 => 'Jeudi',
        5 => 'Vendredi', 6 => 'Samedi', 7 => 'Dimanche'
    ];

    /**
     * Génère la structure complète d'un calendrier mensuel
     *
     * @param int $year L'année (ex: 2026)
     * @param int $month Le mois (1-12)
     * @param array<int, array<string, mixed>> $events Liste des événements du mois
     * @return array{
     *   year: int,
     *   month: int,
     *   monthName: string,
     *   days: array<int, array{
     *     number: int,
     *     date: string,
     *     isCurrentMonth: bool,
     *     isToday: bool,
     *     dayOfWeek: int,
     *     events: array<int, array<string, mixed>>
     *   }>,
     *   prevYear: int,
     *   prevMonth: int,
     *   nextYear: int,
     *   nextMonth: int
     * }
     */
    public function generateMonthCalendar(int $year, int $month, array $events = []): array
    {
        // Validation des paramètres
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Le mois doit être entre 1 et 12");
        }

        // Calculs de base
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfWeek = $this->getFirstDayOfWeek($year, $month);
        
        // Grouper les événements par date
        $eventsByDate = $this->groupEventsByDate($events);
        
        // Construire le tableau des jours
        $days = [];
        
        // 1. Cases vides au début (jours du mois précédent)
        $emptyDaysAtStart = $firstDayOfWeek - 1; // Lundi = 0 cases vides, Dimanche = 6
        $prevMonth = $month - 1;
        $prevYear = $year;
        if ($prevMonth < 1) {
            $prevMonth = 12;
            $prevYear--;
        }
        $daysInPrevMonth = cal_days_in_month(CAL_GREGORIAN, $prevMonth, $prevYear);
        
        for ($i = $emptyDaysAtStart; $i > 0; $i--) {
            $dayNumber = $daysInPrevMonth - $i + 1;
            $date = sprintf('%04d-%02d-%02d', $prevYear, $prevMonth, $dayNumber);
            $days[] = [
                'number' => $dayNumber,
                'date' => $date,
                'isCurrentMonth' => false,
                'isToday' => false,
                'dayOfWeek' => ($emptyDaysAtStart - $i + 1),
                'events' => []
            ];
        }
        
        // 2. Jours du mois actuel
        $today = date('Y-m-d');
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            $dayOfWeek = $timestamp !== false ? (int)date('N', $timestamp) : 1;
            
            $days[] = [
                'number' => $day,
                'date' => $date,
                'isCurrentMonth' => true,
                'isToday' => $date === $today,
                'dayOfWeek' => $dayOfWeek,
                'events' => $eventsByDate[$date] ?? []
            ];
        }
        
        // 3. Cases vides à la fin (jours du mois suivant)
        $totalDays = count($days);
        $emptyDaysAtEnd = (7 - ($totalDays % 7)) % 7;
        
        $nextMonth = $month + 1;
        $nextYear = $year;
        if ($nextMonth > 12) {
            $nextMonth = 1;
            $nextYear++;
        }
        
        for ($day = 1; $day <= $emptyDaysAtEnd; $day++) {
            $date = sprintf('%04d-%02d-%02d', $nextYear, $nextMonth, $day);
            $days[] = [
                'number' => $day,
                'date' => $date,
                'isCurrentMonth' => false,
                'isToday' => false,
                'dayOfWeek' => (($totalDays + $day - 1) % 7) + 1,
                'events' => []
            ];
        }
        
        return [
            'year' => $year,
            'month' => $month,
            'monthName' => self::MONTH_NAMES[$month],
            'days' => $days,
            'prevYear' => $prevYear,
            'prevMonth' => $prevMonth,
            'nextYear' => $nextYear,
            'nextMonth' => $nextMonth
        ];
    }

    /**
     * Calcule le jour de la semaine du 1er du mois
     *
     * @param int $year L'année
     * @param int $month Le mois (1-12)
     * @return int Le jour de la semaine (1=Lundi, 7=Dimanche)
     */
    private function getFirstDayOfWeek(int $year, int $month): int
    {
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        return $timestamp !== false ? (int)date('N', $timestamp) : 1;
    }

    /**
     * Groupe les événements par date
     *
     * @param array<int, array<string, mixed>> $events Liste d'événements
     * @return array<string, array<int, array<string, mixed>>> Événements groupés par date (YYYY-MM-DD)
     */
    private function groupEventsByDate(array $events): array
    {
        $grouped = [];
        
        foreach ($events as $event) {
            // Extraire la date de l'événement
            $eventDate = null;
            
            if (isset($event['event_date'])) {
                $eventDate = $event['event_date'];
            } elseif (isset($event['date'])) {
                $eventDate = $event['date'];
            } elseif (isset($event['start'])) {
                // Format ISO ou datetime
                $eventDate = date('Y-m-d', strtotime($event['start']));
            }
            
            if ($eventDate) {
                // Normaliser au format YYYY-MM-DD
                $normalizedDate = date('Y-m-d', strtotime($eventDate));
                
                if (!isset($grouped[$normalizedDate])) {
                    $grouped[$normalizedDate] = [];
                }
                
                $grouped[$normalizedDate][] = $event;
            }
        }
        
        return $grouped;
    }

    /**
     * Obtient le nom du mois
     *
     * @param int $month Le mois (1-12)
     * @return string Le nom du mois en français
     */
    public function getMonthName(int $month): string
    {
        return self::MONTH_NAMES[$month] ?? '';
    }

    /**
     * Obtient le nom du jour de la semaine
     *
     * @param int $dayOfWeek Le jour de la semaine (1-7)
     * @return string Le nom du jour en français
     */
    public function getWeekdayName(int $dayOfWeek): string
    {
        return self::WEEKDAY_NAMES[$dayOfWeek] ?? '';
    }

    /**
     * Obtient la liste des noms de jours de la semaine
     *
     * @return array<int, string> Les noms des jours (1=Lundi à 7=Dimanche)
     */
    public function getWeekdayNames(): array
    {
        return self::WEEKDAY_NAMES;
    }
}
