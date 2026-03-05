<?php

/**
 * Native Event Calendar HTML Template
 *
 * Expected variables:
 * - $calendar : array returned by CalendarManager::generateMonthCalendar()
 * - $pageUrl : string - Base URL (e.g., 'index.php?page=event')
 * - $extraParams : array - Additional URL parameters
 */

// Valeurs par défaut si non définies
$calendar = $calendar ?? ['days' => [], 'monthName' => '', 'year' => date('Y'), 'month' => (int) date('n'), 'prevYear' => (int) date('Y'), 'prevMonth' => (int) date('n') - 1, 'nextYear' => (int) date('Y'), 'nextMonth' => (int) date('n') + 1];
$pageUrl = $pageUrl ?? 'index.php?page=event';
$extraParams = $extraParams ?? [];

// URL construction with parameters
$buildUrl = function ($year, $month) use ($pageUrl, $extraParams) {
    $params = array_merge($extraParams, [
        'view' => 'calendar',
        'calyear' => $year,
        'calmonth' => $month
    ]);
    return $pageUrl . '&' . http_build_query($params);
};
?>

<link rel="stylesheet" href="./assets/css/pages/calendar.css">

<div class="calendar-container event-calendar" role="region"
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
                aria-label="Retour à aujourd'hui" data-view="calendar" data-calyear="<?= $currentYear ?>"
                data-calmonth="<?= $currentMonth ?>">
                Aujourd'hui
            </a>
        <?php endif; ?>

        <!-- Center: Navigation Controls -->
        <div class="calendar-nav-center">
            <?php
            $prevUrl = $buildUrl($calendar['prevYear'], $calendar['prevMonth']);
            $nextUrl = $buildUrl($calendar['nextYear'], $calendar['nextMonth']);
            $currentUrl = $buildUrl($calendar['year'], $calendar['month']);
            $todayUrl = $buildUrl($currentYear, $currentMonth);

            // Vérifier si le bouton précédent pointe vers la page actuelle
            $isPrevCurrent = ($prevUrl === $currentUrl);
            // Vérifier si le bouton suivant pointe vers la page actuelle ou vers "Aujourd'hui"
            $isNextCurrent = ($nextUrl === $currentUrl || $nextUrl === $todayUrl);
            ?>

            <?php if ($isPrevCurrent) : ?>
                <span class="nav-btn icon-btn disabled" aria-label="Vous êtes déjà sur ce mois">
                    <i class="fas fa-chevron-left"></i>
                </span>
            <?php else : ?>
                <a href="<?= $prevUrl ?>" class="nav-btn icon-btn"
                    aria-label="Mois précédent : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['prevMonth'])) ?> <?= $calendar['prevYear'] ?>"
                    data-view="calendar" data-calyear="<?= $calendar['prevYear'] ?>"
                    data-calmonth="<?= $calendar['prevMonth'] ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            <?php endif; ?>

            <h2 class="calendar-title">
                <?= htmlspecialchars($calendar['monthName']) ?> <?= $calendar['year'] ?>
            </h2>

            <?php if ($isNextCurrent) : ?>
                <span class="nav-btn icon-btn disabled" aria-label="Vous êtes déjà sur ce mois">
                    <i class="fas fa-chevron-right"></i>
                </span>
            <?php else : ?>
                <a href="<?= $nextUrl ?>" class="nav-btn icon-btn"
                    aria-label="Mois suivant : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['nextMonth'])) ?> <?= $calendar['nextYear'] ?>"
                    data-view="calendar" data-calyear="<?= $calendar['nextYear'] ?>"
                    data-calmonth="<?= $calendar['nextMonth'] ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- En-têtes des jours de la semaine -->
    <div class="calendar-weekdays">
        <?php
        $weekdays = ['Lund', 'Mard', 'Mer', 'Jeud', 'Vend', 'Sam', 'Dim'];
        foreach ($weekdays as $day) {
            echo '<div class="calendar-weekday">' . htmlspecialchars($day) . '</div>';
        }
        ?>
    </div>

    <!-- Grille du calendrier -->
    <div class="calendar-grid">
        <?php foreach ($calendar['days'] as $day) : ?>
            <div
                class="calendar-day <?= $day['isCurrentMonth'] ? '' : 'other-month' ?> <?= $day['isToday'] ? 'today' : '' ?>">
                <div class="calendar-day-number"><?= htmlspecialchars((string) $day['number']) ?></div>

                <?php if (!empty($day['events']) && $day['isCurrentMonth']) : ?>
                    <div class="calendar-events">
                        <?php foreach ($day['events'] as $event) : ?>
                            <?php
                            $eventId = $event['id'] ?? null;
                            $eventSlug = $event['slug'] ?? null;
                            $eventTitle = htmlspecialchars($event['title'] ?? '');
                            $eventTime = $event['time'] ?? '';
                            $eventUrl = ($eventSlug !== null && $eventSlug !== '')
                                ? 'index.php?page=showEvent&slug=' . urlencode($eventSlug)
                                : ($eventId ? 'index.php?page=showEvent&id=' . (int) $eventId : '#');

                            // Construire le label accessible
                            $ariaLabel = $eventTitle;
                            if ($eventTime) {
                                $ariaLabel .= ', ' . $eventTime;
                            }
                            ?>
                            <a href="<?= $eventUrl ?>" class="calendar-event" role="button" tabindex="0"
                                data-event-id="<?= htmlspecialchars((string) $eventId) ?>"
                                aria-label="<?= htmlspecialchars($ariaLabel) ?>"
                                title="<?= htmlspecialchars($eventTitle . ($eventTime ? ' - ' . $eventTime : '')) ?>">
                                <?php if ($eventTime) : ?>
                                    <span class="event-time"><?= htmlspecialchars($eventTime) ?></span>
                                <?php endif; ?>
                                <span class="event-title"><?= $eventTitle ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
