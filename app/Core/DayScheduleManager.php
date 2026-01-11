<?php

namespace App\Core;

/**
 * DayScheduleManager - Gestionnaire d'emploi du temps journalier
 *
 * Génère des vues journalières avec timeline verticale détaillée (07:00-20:00)
 * et blocs de cours positionnés selon leur horaire.
 *
 * @package App\Core
 */
class DayScheduleManager
{
    /**
     * Heure de début de la journée (07:00)
     */
    private const START_HOUR = 7;

    /**
     * Heure de fin de la journée (20:00)
     */
    private const END_HOUR = 20;

    /**
     * Génère la structure complète d'un emploi du temps journalier
     *
     * @param string $date Date au format 'Y-m-d'
     * @param array<int, array<string, mixed>> $events Liste des événements du jour
     * @return array{
     *   date: string,
     *   dayName: string,
     *   formatted: string,
     *   events: array<int, array<string, mixed>>,
     *   prevDate: string,
     *   nextDate: string,
     *   hours: array<int, string>,
     *   currentTime: array{hour: int, minute: int, top: int}|null
     * }
     */
    public function generateDaySchedule(string $date, array $events = []): array
    {
        // Parser la date
        $dateObj = new \DateTimeImmutable($date);

        // Informations du jour
        $dayInfo = [
            'date' => $date,
            'dayName' => $this->getFrenchDayName($dateObj->format('N')),
            'formatted' => $dateObj->format('d/m/Y'),
        ];

        // Calculer jour précédent/suivant
        $prevDate = $dateObj->modify('-1 day')->format('Y-m-d');
        $nextDate = $dateObj->modify('+2 days')->format('Y-m-d'); // +2 car on a fait -1 avant

        // Générer la liste des heures
        $hours = [];
        for ($h = self::START_HOUR; $h <= self::END_HOUR; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        // Filtrer et positionner les événements pour ce jour
        $dayEvents = $this->filterAndPositionEvents($events, $date);

        // Calculer position de l'heure actuelle si c'est aujourd'hui
        $currentTime = null;
        if ($date === date('Y-m-d')) {
            $currentTime = $this->calculateCurrentTimePosition();
        }

        return [
            'date' => $date,
            'dayName' => $dayInfo['dayName'],
            'formatted' => $dayInfo['formatted'],
            'events' => $dayEvents,
            'prevDate' => $prevDate,
            'nextDate' => $nextDate,
            'hours' => $hours,
            'currentTime' => $currentTime
        ];
    }

    /**
     * Filtre et positionne les événements pour un jour spécifique
     *
     * @param array<int, array<string, mixed>> $events Tous les événements
     * @param string $targetDate Date cible
     * @return array<int, array<string, mixed>>
     */
    private function filterAndPositionEvents(array $events, string $targetDate): array
    {
        $filtered = [];

        foreach ($events as $event) {
            $eventDate = isset($event['start']) ? substr($event['start'], 0, 10) : '';

            if ($eventDate === $targetDate) {
                // Calculer la position CSS
                $position = $this->calculateEventPosition($event['start'] ?? '', $event['end'] ?? '');
                $event['cssPosition'] = $position;

                $filtered[] = $event;
            }
        }

        // Trier par heure de début
        usort($filtered, function ($a, $b) {
            return strcmp($a['start'] ?? '', $b['start'] ?? '');
        });

        return $filtered;
    }

    /**
     * Calcule la position et hauteur CSS d'un événement
     *
     * @param string $startTime Heure de début
     * @param string $endTime Heure de fin
     * @return array{top: string, height: string}
     */
    private function calculateEventPosition(string $startTime, string $endTime): array
    {
        try {
            $start = new \DateTimeImmutable($startTime);
            $end = new \DateTimeImmutable($endTime);

            $startHour = (int)$start->format('H');
            $startMinute = (int)$start->format('i');
            $endHour = (int)$end->format('H');
            $endMinute = (int)$end->format('i');

            // Calculer les minutes depuis START_HOUR
            $startMinutes = ($startHour - self::START_HOUR) * 60 + $startMinute;
            $endMinutes = ($endHour - self::START_HOUR) * 60 + $endMinute;

            $top = max(0, $startMinutes);
            $height = max(30, $endMinutes - $startMinutes);

            return [
                'top' => $top . 'px',
                'height' => $height . 'px'
            ];
        } catch (\Exception $e) {
            return [
                'top' => '0px',
                'height' => '60px'
            ];
        }
    }

    /**
     * Calcule la position de l'heure actuelle
     *
     * @return array{hour: int, minute: int, top: int}
     */
    private function calculateCurrentTimePosition(): array
    {
        $hour = (int)date('H');
        $minute = (int)date('i');

        // Calculer position en minutes depuis START_HOUR
        $top = (($hour - self::START_HOUR) * 60) + $minute;

        return [
            'hour' => $hour,
            'minute' => $minute,
            'top' => max(0, $top) // S'assurer que c'est positif
        ];
    }

    /**
     * Obtient le nom français du jour de la semaine
     *
     * @param string|int $dayNumber Le jour (1-7, 1=Lundi)
     * @return string
     */
    private function getFrenchDayName($dayNumber): string
    {
        $days = [
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi',
            7 => 'Dimanche'
        ];

        return $days[(int)$dayNumber] ?? 'Lundi';
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
