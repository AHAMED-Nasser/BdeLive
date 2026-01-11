<?php

namespace App\Core;

/**
 * WeeklyScheduleManager - Gestionnaire d'emploi du temps hebdomadaire
 *
 * Génère des vues hebdomadaires avec timeline verticale (07:00-20:00)
 * et blocs de cours positionnés selon leur horaire.
 *
 * @package App\Core
 */
class WeeklyScheduleManager
{
    /**
     * Jours de la semaine
     */
    private const WEEKDAYS = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche'
    ];

    /**
     * Heure de début de la journée (07:00)
     */
    private const START_HOUR = 7;

    /**
     * Heure de fin de la journée (20:00)
     */
    private const END_HOUR = 20;

    /**
     * Génère la structure complète d'un emploi du temps hebdomadaire
     *
     * @param int $year L'année
     * @param int $week Le numéro de semaine (1-53)
     * @param array<int, array<string, mixed>> $events Liste des événements de la semaine
     * @return array{
     *   year: int,
     *   week: int,
     *   weekDates: array<int, array{day: int, date: string, dayName: string, formatted: string}>,
     *   events: array<string, array<int, array<string, mixed>>>,
     *   prevWeek: int,
     *   nextWeek: int,
     *   prevYear: int,
     *   nextYear: int,
     *   hours: array<int, string>
     * }
     */
    public function generateWeeklySchedule(int $year, int $week, array $events = []): array
    {
        // Validation
        if ($week < 1 || $week > 53) {
            throw new \InvalidArgumentException("Le numéro de semaine doit être entre 1 et 53");
        }

        // Calculer les dates de la semaine
        $weekDates = $this->getWeekDates($year, $week);

        // Grouper les événements par jour
        $eventsByDay = $this->groupEventsByDay($events, $weekDates);

        // Calculer semaine précédente/suivante
        $prevWeek = $week - 1;
        $prevYear = $year;
        if ($prevWeek < 1) {
            $prevWeek = 52; // Approximation
            $prevYear--;
        }

        $nextWeek = $week + 1;
        $nextYear = $year;
        if ($nextWeek > 52) {
            $nextWeek = 1;
            $nextYear++;
        }

        // Générer la liste des heures
        $hours = [];
        for ($h = self::START_HOUR; $h <= self::END_HOUR; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        return [
            'year' => $year,
            'week' => $week,
            'weekDates' => $weekDates,
            'events' => $eventsByDay,
            'prevWeek' => $prevWeek,
            'nextWeek' => $nextWeek,
            'prevYear' => $prevYear,
            'nextYear' => $nextYear,
            'hours' => $hours
        ];
    }

    /**
     * Calcule les dates des jours de la semaine
     *
     * @param int $year L'année
     * @param int $week Le numéro de semaine
     * @return array<int, array{day: int, date: string, dayName: string, formatted: string}>
     */
    private function getWeekDates(int $year, int $week): array
    {
        $dates = [];

        // Créer une date au début de l'année
        $dto = new \DateTime();
        $dto->setISODate($year, $week, 1); // ISO: 1 = Lundi

        // Générer Lundi à Vendredi (ou Dimanche selon besoin)
        for ($day = 1; $day <= 5; $day++) { // Lun-Ven
            $dates[$day] = [
                'day' => $day,
                'date' => $dto->format('Y-m-d'),
                'dayName' => self::WEEKDAYS[$day],
                'formatted' => $dto->format('d/m') // Ex: 02/12
            ];
            $dto->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Groupe les événements par jour de la semaine
     *
     * @param array<int, array<string, mixed>> $events Liste d'événements
     * @param array<int, array{date: string}> $weekDates Dates de la semaine
     * @return array<string, array<int, array<string, mixed>>> Événements groupés par date
     */
    private function groupEventsByDay(array $events, array $weekDates): array
    {
        $grouped = [];

        // Initialiser avec les dates de la semaine
        foreach ($weekDates as $dayInfo) {
            $grouped[$dayInfo['date']] = [];
        }

        // Grouper les événements
        foreach ($events as $event) {
            // Extraire la date de début
            $eventDate = null;
            if (isset($event['start'])) {
                $eventDate = substr($event['start'], 0, 10); // YYYY-MM-DD
            } elseif (isset($event['date'])) {
                $eventDate = date('Y-m-d', strtotime($event['date']));
            }

            if ($eventDate && isset($grouped[$eventDate])) {
                // Calculer la position CSS
                $position = $this->calculateEventPosition($event['start'] ?? '', $event['end'] ?? '');
                $event['cssPosition'] = $position;

                $grouped[$eventDate][] = $event;
            }
        }

        return $grouped;
    }

    /**
     * Calcule la position et hauteur CSS d'un événement
     *
     * @param string $startTime Heure de début (format: "YYYY-MM-DD HH:MM:SS" ou "HH:MM")
     * @param string $endTime Heure de fin
     * @return array{top: string, height: string}
     */
    public function calculateEventPosition(string $startTime, string $endTime): array
    {
        try {
            // Parser les timestamps
            $start = new \DateTimeImmutable($startTime);
            $end = new \DateTimeImmutable($endTime);

            // Extraire heures et minutes
            $startHour = (int)$start->format('H');
            $startMinute = (int)$start->format('i');
            $endHour = (int)$end->format('H');
            $endMinute = (int)$end->format('i');

            // Calculer les minutes depuis START_HOUR (07:00)
            $startMinutes = ($startHour - self::START_HOUR) * 60 + $startMinute;
            $endMinutes = ($endHour - self::START_HOUR) * 60 + $endMinute;

            // Convertir en pixels (1 minute = 1 pixel avec PIXELS_PER_HOUR = 60)
            $top = $startMinutes;
            $height = $endMinutes - $startMinutes;

            // S'assurer que les valeurs sont positives
            $top = max(0, $top);
            $height = max(30, $height); // Hauteur minimale de 30px

            return [
                'top' => $top . 'px',
                'height' => $height . 'px'
            ];
        } catch (\Exception $e) {
            // Valeurs par défaut en cas d'erreur
            return [
                'top' => '0px',
                'height' => '60px'
            ];
        }
    }

    /**
     * Obtient le nom du jour de la semaine
     *
     * @param int $day Le jour (1-7, 1=Lundi)
     * @return string Le nom du jour
     */
    public function getWeekdayName(int $day): string
    {
        return self::WEEKDAYS[$day] ?? '';
    }

    /**
     * Obtient la plage horaire de la timeline
     *
     * @return array{start: int, end: int}
     */
    public function getTimeRange(): array
    {
        return [
            'start' => self::START_HOUR,
            'end' => self::END_HOUR
        ];
    }
}
