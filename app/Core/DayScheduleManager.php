<?php

namespace App\Core;

/**
 * DayScheduleManager - Daily Schedule Manager
 *
 * Generates daily views with detailed vertical timeline (08:00-20:00)
 * and course blocks positioned according to their schedule.
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core
 * @version 1.0.0
 */
class DayScheduleManager
{
    /**
     * Start hour of the day (08:00)
     */
    private const START_HOUR = 8;

    /**
     * End hour of the day (20:00)
     */
    private const END_HOUR = 20;

    /**
     * Generates the complete structure of a daily schedule
     *
     * @param string $date Date in 'Y-m-d' format
     * @param array<int, array<string, mixed>> $events List of events for the day
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
        // Parse the date
        $dateObj = new \DateTimeImmutable($date);

        // Day information
        $dayInfo = [
            'date' => $date,
            'dayName' => $this->getFrenchDayName($dateObj->format('N')),
            'formatted' => $dateObj->format('d/m/Y'),
        ];

        // Calculate previous/next day
        $prevDate = $dateObj->modify('-1 day')->format('Y-m-d');
        $nextDate = $dateObj->modify('+2 days')->format('Y-m-d'); // +2 car on a fait -1 avant

        // Generate the list of hours
        $hours = [];
        for ($h = self::START_HOUR; $h <= self::END_HOUR; $h++) {
            $hours[] = sprintf('%02d:00', $h);
        }

        // Filter and position events for this day
        $dayEvents = $this->filterAndPositionEvents($events, $date);

        // Calculate current time position if it's today
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
     * Filters and positions events for a specific day
     *
     * @param array<int, array<string, mixed>> $events All events
     * @param string $targetDate Target date
     * @return array<int, array<string, mixed>>
     */
    private function filterAndPositionEvents(array $events, string $targetDate): array
    {
        $filtered = [];

        foreach ($events as $event) {
            $eventDate = isset($event['start']) ? substr($event['start'], 0, 10) : '';

            if ($eventDate === $targetDate) {
                // Calculate CSS position
                $position = $this->calculateEventPosition($event['start'] ?? '', $event['end'] ?? '');
                $event['cssPosition'] = $position;

                $filtered[] = $event;
            }
        }

        // Sort by start time
        usort($filtered, function ($a, $b) {
            return strcmp($a['start'] ?? '', $b['start'] ?? '');
        });

        return $filtered;
    }

    /**
     * Calculates the CSS position and height of an event
     *
     * @param string $startTime Start time
     * @param string $endTime End time
     * @return array{top: string, height: string}
     */
    private function calculateEventPosition(string $startTime, string $endTime): array
    {
        try {
            $start = new \DateTimeImmutable($startTime);
            $end = new \DateTimeImmutable($endTime);

            $startHour = (int) $start->format('H');
            $startMinute = (int) $start->format('i');
            $endHour = (int) $end->format('H');
            $endMinute = (int) $end->format('i');

            // Calculate minutes since START_HOUR
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
     * Calculates the current time position
     *
     * @return array{hour: int, minute: int, top: int}
     */
    private function calculateCurrentTimePosition(): array
    {
        $hour = (int) date('H');
        $minute = (int) date('i');

        // Calculate position in minutes since START_HOUR
        $top = (($hour - self::START_HOUR) * 60) + $minute;

        return [
            'hour' => $hour,
            'minute' => $minute,
            'top' => max(0, $top) // Ensure it's positive
        ];
    }

    /**
     * Gets the French name of the day of the week
     *
     * @param string|int $dayNumber The day (1-7, 1=Monday)
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

        return $days[(int) $dayNumber] ?? 'Lundi';
    }

    /**
     * Gets the timeline time range
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
