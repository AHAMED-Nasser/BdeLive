<?php

declare(strict_types=1);

namespace App\Core;

/**
 * WeeklyScheduleManager - Weekly Schedule Manager
 *
 * Generates weekly views with vertical timeline (08:00-20:00)
 * and course blocks positioned according to their schedule.
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core
 * @version 1.0.0
 */
class WeeklyScheduleManager
{
    /**
     * Days of the week
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
     * Start hour of the day (08:00)
     */
    private const START_HOUR = 8;

    /**
     * End hour of the day (20:00)
     */
    private const END_HOUR = 20;

    /**
     * Generates the complete structure of a weekly schedule
     *
     * @param int $year The year
     * @param int $week The week number (1-53)
     * @param array<int, array<string, mixed>> $events List of events for the week
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
            throw new \InvalidArgumentException("Week number must be between 1 and 53");
        }

        // Calculate week dates
        $weekDates = $this->getWeekDates($year, $week);

        // Group events by day
        $eventsByDay = $this->groupEventsByDay($events, $weekDates);

        // Calculate previous/next week (certaines années ont une semaine 53)
        $prevWeek = $week - 1;
        $prevYear = $year;
        if ($prevWeek < 1) {
            $prevYear--;
            // Vérifier combien de semaines a l'année précédente
            $prevWeek = (int) (new \DateTime())->setISODate($prevYear, 53, 1)->format('W') === 53
                ? 53
                : 52;
        }

        $nextWeek = $week + 1;
        $nextYear = $year;
        $maxWeeksThisYear = (int) (new \DateTime())->setISODate($year, 53, 1)->format('W') === 53
            ? 53
            : 52;
        if ($nextWeek > $maxWeeksThisYear) {
            $nextWeek = 1;
            $nextYear++;
        }

        // Generate the list of hours
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
     * Calculates the dates of the days of the week
     *
     * @param int $year The year
     * @param int $week The week number
     * @return array<int, array{day: int, date: string, dayName: string, formatted: string}>
     */
    private function getWeekDates(int $year, int $week): array
    {
        $dates = [];

        // Create a date at the beginning of the year
        $dto = new \DateTime();
        $dto->setISODate($year, $week, 1); // ISO: 1 = Monday

        // Generate Monday to Friday (or Sunday as needed)
        for ($day = 1; $day <= 5; $day++) { // Mon-Fri
            $dates[$day] = [
                'day' => $day,
                'date' => $dto->format('Y-m-d'),
                'dayName' => self::WEEKDAYS[$day],
                'formatted' => $dto->format('d/m') // e.g., 02/12
            ];
            $dto->modify('+1 day');
        }

        return $dates;
    }

    /**
     * Groups events by day of the week
     *
     * @param array<int, array<string, mixed>> $events List of events
     * @param array<int, array{date: string}> $weekDates Week dates
     * @return array<string, array<int, array<string, mixed>>> Events grouped by date
     */
    private function groupEventsByDay(array $events, array $weekDates): array
    {
        $grouped = [];

        // Initialize with week dates
        foreach ($weekDates as $dayInfo) {
            $grouped[$dayInfo['date']] = [];
        }

        // Grouper les événements
        foreach ($events as $event) {
            // Extract start date
            $eventDate = null;
            if (isset($event['start'])) {
                $eventDate = substr($event['start'], 0, 10); // YYYY-MM-DD
            } elseif (isset($event['date'])) {
                $eventDate = date('Y-m-d', strtotime($event['date']));
            }

            if ($eventDate && isset($grouped[$eventDate])) {
                // Calculate CSS position
                $position = $this->calculateEventPosition($event['start'] ?? '', $event['end'] ?? '');
                $event['cssPosition'] = $position;

                $grouped[$eventDate][] = $event;
            }
        }

        return $grouped;
    }

    /**
     * Calculates the CSS position and height of an event
     *
     * @param string $startTime Start time (format: "YYYY-MM-DD HH:MM:SS" or "HH:MM")
     * @param string $endTime End time
     * @return array{top: string, height: string}
     */
    public function calculateEventPosition(string $startTime, string $endTime): array
    {
        try {
            // Parse timestamps
            $start = new \DateTimeImmutable($startTime);
            $end = new \DateTimeImmutable($endTime);

            // Extract hours and minutes
            $startHour = (int) $start->format('H');
            $startMinute = (int) $start->format('i');
            $endHour = (int) $end->format('H');
            $endMinute = (int) $end->format('i');

            // Calculate minutes since START_HOUR (08:00)
            $startMinutes = ($startHour - self::START_HOUR) * 60 + $startMinute;
            $endMinutes = ($endHour - self::START_HOUR) * 60 + $endMinute;

            // Convert to pixels (1 minute = 1 pixel with PIXELS_PER_HOUR = 60)
            $top = $startMinutes;
            $height = $endMinutes - $startMinutes;

            // Ensure values are positive
            $top = max(0, $top);
            $height = max(30, $height); // Minimum height of 30px

            return [
                'top' => $top . 'px',
                'height' => $height . 'px'
            ];
        } catch (\Exception $e) {
            // Default values in case of error
            return [
                'top' => '0px',
                'height' => '60px'
            ];
        }
    }

    /**
     * Gets the weekday name
     *
     * @param int $day The day (1-7, 1=Monday)
     * @return string The day name
     */
    public function getWeekdayName(int $day): string
    {
        return self::WEEKDAYS[$day] ?? '';
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
