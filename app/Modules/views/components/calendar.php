<?php

/**
 * Native Calendar HTML Template
 *
 * Expected variables:
 * - $calendar : array returned by CalendarManager::generateMonthCalendar()
 * - $pageUrl : string - Base URL (e.g., 'index.php?page=schedule')
 * - $extraParams : array - Additional URL parameters (e.g., ['group' => 'GA2-2'])
 */

// Default values if not defined
$calendar = $calendar ?? ['days' => [], 'monthName' => '', 'year' => date('Y'), 'month' => (int) date('n'), 'prevYear' => (int) date('Y'), 'prevMonth' => (int) date('n') - 1, 'nextYear' => (int) date('Y'), 'nextMonth' => (int) date('n') + 1];
$pageUrl = $pageUrl ?? 'index.php';
$extraParams = $extraParams ?? [];

// URL construction with parameters (IMPORTANT: use calyear/calmonth to avoid collision with group year)
$buildUrl = function ($year, $month) use ($pageUrl, $extraParams) {
    $params = array_merge($extraParams, [
        'view' => 'month', // Force month view
        'calyear' => $year,
        'calmonth' => $month
    ]);
    return $pageUrl . '&' . http_build_query($params);
};
?>

<link rel="stylesheet" href="./assets/css/pages/calendar.css">
<link rel="stylesheet" href="./assets/css/pages/weekly-schedule.css">
<!-- Script modal requis pour les événements -->
<script src="./assets/js/schedule-modal.js" defer></script>

<div class="calendar-container" role="region"
    aria-label="Calendrier <?= htmlspecialchars($calendar['monthName']) ?> <?= $calendar['year'] ?>">

    <!-- Toolbar Navigation Pro -->
    <div class="calendar-header">
        <!-- Left: Today Button -->
        <?php
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('n');
        $isCurrentMonth = ($calendar['year'] == $currentYear && $calendar['month'] == $currentMonth);
        ?>
        <?php if ($isCurrentMonth) : ?>
            <span class="nav-btn today-btn disabled" aria-label="Vous êtes déjà sur le mois actuel">
                Aujourd'hui
            </span>
        <?php else : ?>
            <a href="<?= $buildUrl($currentYear, $currentMonth) ?>" class="nav-btn today-btn"
                aria-label="Retour à aujourd'hui" data-view="month" data-calyear="<?= $currentYear ?>"
                data-calmonth="<?= $currentMonth ?>">
                Aujourd'hui
            </a>
        <?php endif; ?>

        <!-- Center: Navigation Controls -->
        <div class="calendar-nav-center">
            <a href="<?= $buildUrl($calendar['prevYear'], $calendar['prevMonth']) ?>" class="nav-btn icon-btn"
                aria-label="Mois précédent : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['prevMonth'])) ?> <?= $calendar['prevYear'] ?>"
                data-view="month" data-calyear="<?= $calendar['prevYear'] ?>"
                data-calmonth="<?= $calendar['prevMonth'] ?>">
                <i class="fas fa-chevron-left"></i>
            </a>

            <h2 class="calendar-title">
                <?= htmlspecialchars($calendar['monthName']) ?> <?= $calendar['year'] ?>
            </h2>

            <a href="<?= $buildUrl($calendar['nextYear'], $calendar['nextMonth']) ?>" class="nav-btn icon-btn"
                aria-label="Mois suivant : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['nextMonth'])) ?> <?= $calendar['nextYear'] ?>"
                data-view="month" data-calyear="<?= $calendar['nextYear'] ?>"
                data-calmonth="<?= $calendar['nextMonth'] ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <!-- Right: View Switcher -->
        <div class="view-switcher">
            <a href="<?= $pageUrl ?>&view=day&date=<?= date('Y-m-d') ?>&<?= http_build_query($extraParams) ?>"
                class="view-btn" data-view="day">
                Jour
            </a>
            <a href="<?= $pageUrl ?>&view=week&<?= http_build_query($extraParams) ?>" class="view-btn" data-view="week">
                Semaine
            </a>
            <a href="<?= $pageUrl ?>&view=month&<?= http_build_query($extraParams) ?>" class="view-btn active"
                data-view="month">
                Mois
            </a>
        </div>
    </div>

    <!-- En-têtes des jours de la semaine -->
    <div class="calendar-weekdays">
        <div class="calendar-weekday" aria-label="Lundi">Lun</div>
        <div class="calendar-weekday" aria-label="Mardi">Mar</div>
        <div class="calendar-weekday" aria-label="Mercredi">Mer</div>
        <div class="calendar-weekday" aria-label="Jeudi">Jeu</div>
        <div class="calendar-weekday" aria-label="Vendredi">Ven</div>
        <div class="calendar-weekday" aria-label="Samedi">Sam</div>
        <div class="calendar-weekday" aria-label="Dimanche">Dim</div>
    </div>

    <!-- Grille des jours -->
    <div class="calendar-grid">
        <?php foreach ($calendar['days'] as $day) : ?>
            <?php
            $dayClasses = ['calendar-day'];
            if ($day['isCurrentMonth']) {
                $dayClasses[] = 'current-month';
            } else {
                $dayClasses[] = 'other-month';
            }
            if ($day['isToday']) {
                $dayClasses[] = 'today';
            }

            // ARIA label descriptif
            $dayLabel = $day['number'];
            if ($day['isToday']) {
                $dayLabel .= ' (aujourd\'hui)';
            }
            if (!empty($day['events'])) {
                $eventCount = count($day['events']);
                $dayLabel .= ', ' . $eventCount . ' événement' . ($eventCount > 1 ? 's' : '');
            }
            ?>

            <div class="<?= implode(' ', $dayClasses) ?>" data-date="<?= htmlspecialchars($day['date']) ?>"
                aria-label="<?= htmlspecialchars($dayLabel) ?>">

                <div class="calendar-day-number"><?= $day['number'] ?></div>

                <?php if (!empty($day['events'])) : ?>
                    <div class="calendar-events">
                        <?php foreach ($day['events'] as $event) : ?>
                            <?php
                            // Déterminer la couleur de l'événement
                            $eventColor = $event['color'] ?? $event['backgroundColor'] ?? '#3788d8';
                            $eventType = $event['type'] ?? 'default';

                            // Extraire l'heure et durée
                            $start = '';
                            $end = '';
                            if (isset($event['start'])) {
                                $start = date('H:i', strtotime($event['start']));
                            }
                            if (isset($event['end'])) {
                                $end = date('H:i', strtotime($event['end']));
                            }
                            $timeRange = ($start && $end) ? "$start - $end" : $start;

                            // Titre et détails
                            $eventTitle = $event['event_name'] ?? $event['title'] ?? 'Événement';
                            $location = $event['location'] ?? '';
                            $teacher = $event['teacher'] ?? '';

                            // ARIA label complet
                            $eventAriaLabel = "$eventTitle";
                            if ($timeRange) {
                                $eventAriaLabel .= " à $timeRange";
                            }
                            if ($location) {
                                $eventAriaLabel .= ", salle $location";
                            }
                            ?>

                            <div class="calendar-event" role="button" tabindex="0" data-type="<?= htmlspecialchars($eventType) ?>"
                                data-time="<?= htmlspecialchars($timeRange) ?>" data-title="<?= htmlspecialchars($eventTitle) ?>"
                                data-location="<?= htmlspecialchars($location) ?>" data-teacher="<?= htmlspecialchars($teacher) ?>"
                                style="background-color: <?= htmlspecialchars($eventColor) ?>;"
                                aria-label="<?= htmlspecialchars($eventAriaLabel) ?>"
                                title="<?= htmlspecialchars($eventTitle . ($timeRange ? ' - ' . $timeRange : '') . ($location ? ' - ' . $location : '')) ?>">
                                <?php if ($start) : ?>
                                    <span class="event-time"><?= htmlspecialchars($start) ?></span>
                                <?php endif; ?>
                                <span class="event-title"><?= htmlspecialchars($eventTitle) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Mobile (Réutilisée) -->
<div id="course-modal" class="course-modal" role="dialog" aria-hidden="true">
    <div class="modal-overlay" tabindex="0" role="button" aria-label="Fermer la modal"></div>
    <div class="modal-content">
        <button class="modal-close" aria-label="Fermer">✕</button>
        <div class="modal-body">
            <div class="modal-time" id="modal-time"></div>
            <div class="modal-title" id="modal-title"></div>
            <div class="modal-location" id="modal-location"></div>
            <div class="modal-teacher" id="modal-teacher"></div>
        </div>
    </div>
</div>