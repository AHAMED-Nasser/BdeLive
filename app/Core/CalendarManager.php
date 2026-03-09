<?php

declare(strict_types=1);

namespace App\Core;

/**
 * CalendarManager - Native Calendar Manager
 *
 * Generates monthly calendars in CSS Grid format without external dependencies.
 * Replaces FullCalendar for better accessibility and performance.
 *
 * @author BDELIVE - Groupe 8
 * @package App\Core
 * @version 1.0.0
 */
class CalendarManager
{
    /**
     * French month names
     */
    private const MONTH_NAMES = [
        1 => 'Janvier',
        2 => 'Février',
        3 => 'Mars',
        4 => 'Avril',
        5 => 'Mai',
        6 => 'Juin',
        7 => 'Juillet',
        8 => 'Août',
        9 => 'Septembre',
        10 => 'Octobre',
        11 => 'Novembre',
        12 => 'Décembre'
    ];

    /**
     * Weekday names (Monday = 1, Sunday = 7)
     */
    private const WEEKDAY_NAMES = [
        1 => 'Lundi',
        2 => 'Mardi',
        3 => 'Mercredi',
        4 => 'Jeudi',
        5 => 'Vendredi',
        6 => 'Samedi',
        7 => 'Dimanche'
    ];

    /**
     * Generates the complete structure of a monthly calendar
     *
     * @param int $year The year (e.g., 2026)
     * @param int $month The month (1-12)
     * @param array<int, array<string, mixed>> $events List of events for the month
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
        // Parameter validation
        if ($month < 1 || $month > 12) {
            throw new \InvalidArgumentException("Month must be between 1 and 12");
        }

        // Basic calculations
        $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $firstDayOfWeek = $this->getFirstDayOfWeek($year, $month);

        // Group events by date
        $eventsByDate = $this->groupEventsByDate($events);

        // Build the days array
        $days = [];

        // 1. Empty cells at the beginning (previous month days)
        $emptyDaysAtStart = $firstDayOfWeek - 1; // Monday = 0 empty cells, Sunday = 6
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

        // 2. Current month days
        $today = date('Y-m-d');
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $timestamp = mktime(0, 0, 0, $month, $day, $year);
            $dayOfWeek = $timestamp !== false ? (int) date('N', $timestamp) : 1;

            $days[] = [
                'number' => $day,
                'date' => $date,
                'isCurrentMonth' => true,
                'isToday' => $date === $today,
                'dayOfWeek' => $dayOfWeek,
                'events' => $eventsByDate[$date] ?? []
            ];
        }

        // 3. Empty cells at the end (next month days)
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
     * Calculates the day of the week for the first day of the month
     *
     * @param int $year The year
     * @param int $month The month (1-12)
     * @return int The day of the week (1=Monday, 7=Sunday)
     */
    private function getFirstDayOfWeek(int $year, int $month): int
    {
        $timestamp = mktime(0, 0, 0, $month, 1, $year);
        return $timestamp !== false ? (int) date('N', $timestamp) : 1;
    }

    /**
     * Groups events by date
     *
     * @param array<int, array<string, mixed>> $events List of events
     * @return array<string, array<int, array<string, mixed>>> Events grouped by date (YYYY-MM-DD)
     */
    private function groupEventsByDate(array $events): array
    {
        $grouped = [];

        foreach ($events as $event) {
            // Extract the event date
            $eventDate = null;

            if (isset($event['event_date'])) {
                $eventDate = $event['event_date'];
            } elseif (isset($event['date'])) {
                $eventDate = $event['date'];
            } elseif (isset($event['start'])) {
                // ISO or datetime format
                $eventDate = date('Y-m-d', strtotime($event['start']));
            }

            if ($eventDate) {
                // Normalize to YYYY-MM-DD format
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
     * Gets the month name
     *
     * @param int $month The month (1-12)
     * @return string The month name in French
     */
    public function getMonthName(int $month): string
    {
        return self::MONTH_NAMES[$month] ?? '';
    }

    /**
     * Gets the weekday name
     *
     * @param int $dayOfWeek The day of the week (1-7)
     * @return string The day name in French
     */
    public function getWeekdayName(int $dayOfWeek): string
    {
        return self::WEEKDAY_NAMES[$dayOfWeek] ?? '';
    }

    /**
     * Gets the list of weekday names
     *
     * @return array<int, string> The day names (1=Monday to 7=Sunday)
     */
    public function getWeekdayNames(): array
    {
        return self::WEEKDAY_NAMES;
    }
}
