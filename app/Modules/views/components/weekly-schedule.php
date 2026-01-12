<?php

/**
 * Template HTML pour la vue hebdomadaire d'emploi du temps
 *
 * Variables attendues :
 * - $schedule : array retourné par WeeklyScheduleManager::generateWeeklySchedule()
 * - $pageUrl : string - URL de base (ex: 'index.php?page=schedule')
 * - $extraParams : array - Paramètres URL supplémentaires
 */

// Valeurs par défaut si non définies
$schedule = $schedule ?? [
    'weekDates' => [],
    'events' => [],
    'hours' => [],
    'year' => date('Y'),
    'week' => (int) date('W'),
    'prevYear' => (int) date('Y'),
    'prevWeek' => (int) date('W') - 1,
    'nextYear' => (int) date('Y'),
    'nextWeek' => (int) date('W') + 1
];
$pageUrl = $pageUrl ?? 'index.php';
$extraParams = $extraParams ?? [];

// Construction URL navigation
$buildUrl = function ($year, $week) use ($pageUrl, $extraParams) {
    $params = array_merge($extraParams, [
        'calyear' => $year,
        'week' => $week
    ]);
    return $pageUrl . '&' . http_build_query($params);
};

// Formater la période de la semaine
$firstDate = !empty($schedule['weekDates']) ? reset($schedule['weekDates']) : null;
$lastDate = !empty($schedule['weekDates']) ? end($schedule['weekDates']) : null;
$weekPeriod = $firstDate && $lastDate
    ? $firstDate['formatted'] . ' - ' . $lastDate['formatted']
    : 'Semaine ' . $schedule['week'];

// Calculer position de l'heure actuelle (si aujourd'hui dans la semaine)
$currentTimePosition = null;
$currentTimeLabel = '';
if (!empty($schedule['weekDates'])) {
    $today = date('Y-m-d');
    foreach ($schedule['weekDates'] as $dayInfo) {
        if ($dayInfo['date'] === $today) {
            $hour = (int) date('H');
            $minute = (int) date('i');
            $currentTimePosition = (($hour - 8) * 60) + $minute; // Minutes depuis 08:00
            $currentTimeLabel = date('H:i');
            break;
        }
    }
}
?>

<link rel="stylesheet" href="./assets/css/weekly-schedule.css">
<link rel="stylesheet" href="   ./assets/css/calendar.css">
<script src="./assets/js/schedule-modal.js" defer></script>

<div class="weekly-schedule" role="region" aria-label="Emploi du temps semaine <?= $schedule['week'] ?>">

    <!-- Toolbar Navigation Pro -->
    <div class="calendar-header">
        <!-- Left: Today Button -->
        <?php
        $todayWeek = (int) date('W');
        $todayYear = (int) date('Y');
        $todayUrl = $pageUrl . '&view=week&' . http_build_query(array_merge($extraParams, ['calyear' => $todayYear, 'week' => $todayWeek]));
        ?>
        <a href="<?= $todayUrl ?>" class="nav-btn today-btn" aria-label="Retour à aujourd'hui" data-view="week"
            data-calyear="<?= $todayYear ?>" data-week="<?= $todayWeek ?>">
            Aujourd'hui
        </a>

        <!-- Center: Navigation Controls -->
        <div class="calendar-nav-center">
            <a href="<?= $buildUrl($schedule['prevYear'], $schedule['prevWeek']) ?>" class="nav-btn icon-btn"
                aria-label="Semaine précédente" data-view="week" data-calyear="<?= $schedule['prevYear'] ?>"
                data-week="<?= $schedule['prevWeek'] ?>">
                <i class="fas fa-chevron-left"></i>
            </a>

            <h2 class="calendar-title">
                <?= $weekPeriod ?>, <?= $schedule['year'] ?>
            </h2>

            <a href="<?= $buildUrl($schedule['nextYear'], $schedule['nextWeek']) ?>" class="nav-btn icon-btn"
                aria-label="Semaine suivante" data-view="week" data-calyear="<?= $schedule['nextYear'] ?>"
                data-week="<?= $schedule['nextWeek'] ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <!-- Right: View Switcher -->
        <div class="view-switcher">
            <a href="<?= $pageUrl ?>&view=day&date=<?= date('Y-m-d') ?>&<?= http_build_query($extraParams) ?>"
                class="view-btn <?= ($view ?? 'week') === 'day' ? 'active' : '' ?>" data-view="day">
                Jour
            </a>
            <a href="<?= $pageUrl ?>&view=week&<?= http_build_query($extraParams) ?>"
                class="view-btn <?= ($view ?? 'week') === 'week' ? 'active' : '' ?>" data-view="week">
                Semaine
            </a>
            <a href="<?= $pageUrl ?>&view=month&<?= http_build_query($extraParams) ?>"
                class="view-btn <?= ($view ?? 'week') === 'month' ? 'active' : '' ?>" data-view="month">
                Mois
            </a>
        </div>
    </div>

    <!-- Conteneur Grille -->
    <div class="schedule-grid-container">

        <!-- Axe des heures (gauche) -->
        <div class="time-axis" aria-label="Heures">
            <?php foreach ($schedule['hours'] as $hour) : ?>
                <div class="time-slot"><?= htmlspecialchars($hour) ?></div>
            <?php endforeach; ?>
        </div>

        <!-- Grille des jours -->
        <div class="schedule-grid-wrapper">
            <div class="schedule-grid">

                <!-- En-têtes des jours -->
                <?php foreach ($schedule['weekDates'] as $dayInfo) : ?>
                    <div class="day-header">
                        <?= htmlspecialchars($dayInfo['dayName']) ?><br>
                        <small
                            style="font-weight: 400; opacity: 0.8;"><?= htmlspecialchars($dayInfo['formatted']) ?></small>
                    </div>
                <?php endforeach; ?>

                <!-- Colonnes des jours avec événements -->
                <?php foreach ($schedule['weekDates'] as $dayInfo) : ?>
                    <div class="day-column" data-date="<?= htmlspecialchars($dayInfo['date']) ?>">

                        <!-- Lignes horaires (background) -->
                        <?php foreach ($schedule['hours'] as $index => $hour) : ?>
                            <div class="hour-line" style="top: <?= $index * 60 ?>px;"></div>
                        <?php endforeach; ?>

                        <!-- Événements/Cours du jour -->
                        <?php
                        $dayEvents = $schedule['events'][$dayInfo['date']] ?? [];
                        if (!empty($dayEvents)) :
                            foreach ($dayEvents as $event) :
                                // Extraire les infos
                                $title = $event['title'] ?? 'Cours';
                                $location = $event['location'] ?? '';
                                $teacher = $event['teacher'] ?? '';
                                $type = $event['type'] ?? 'default';
                                $cssPos = $event['cssPosition'] ?? ['top' => '0px', 'height' => '60px'];

                                // Formatage des horaires
                                $startTime = isset($event['start']) ? date('H:i', strtotime($event['start'])) : '';
                                $endTime = isset($event['end']) ? date('H:i', strtotime($event['end'])) : '';
                                $timeRange = $startTime && $endTime ? "$startTime - $endTime" : '';

                                // ARIA label complet
                                $ariaLabel = $title;
                                if ($timeRange) {
                                    $ariaLabel .= ", $timeRange";
                                }
                                if ($location) {
                                    $ariaLabel .= ", $location";
                                }
                                if ($teacher) {
                                    $ariaLabel .= ", $teacher";
                                }
                                ?>
                                <div class="course-block" data-type="<?= htmlspecialchars($type) ?>"
                                    data-time="<?= htmlspecialchars($timeRange) ?>" data-title="<?= htmlspecialchars($title) ?>"
                                    data-location="<?= htmlspecialchars($location) ?>"
                                    data-teacher="<?= htmlspecialchars($teacher) ?>"
                                    style="top: <?= htmlspecialchars($cssPos['top']) ?>; height: <?= htmlspecialchars($cssPos['height']) ?>;"
                                    tabindex="0" role="button" aria-label="<?= htmlspecialchars($ariaLabel) ?>">

                                    <?php if ($timeRange) : ?>
                                        <div class="course-time"><?= htmlspecialchars($timeRange) ?></div>
                                    <?php endif; ?>

                                    <div class="course-title"><?= htmlspecialchars($title) ?></div>

                                    <?php if ($location) : ?>
                                        <div class="course-location">📍 <?= htmlspecialchars($location) ?></div>
                                    <?php endif; ?>

                                    <?php if ($teacher) : ?>
                                        <div class="course-teacher">👨‍🏫 <?= htmlspecialchars($teacher) ?></div>
                                    <?php endif; ?>
                                </div>
                                <?php
                            endforeach;
                        endif;
                        ?>
                    </div>
                <?php endforeach; ?>

                <!-- Indicateur de temps actuel (ligne rouge) -->
                <?php if ($currentTimePosition !== null && $currentTimePosition >= 0 && $currentTimePosition <= 780) : ?>
                    <div class="current-time-indicator" style="top: <?= $currentTimePosition ?>px;"
                        data-time="<?= $currentTimeLabel ?>">
                        <span class="time-label"><?= $currentTimeLabel ?></span>
                    </div>
                <?php endif; ?>

            </div>
        </div>

    </div>

</div>

<!-- Modal Mobile pour Détails Cours -->
<div id="course-modal" class="course-modal" role="dialog" aria-hidden="true" aria-labelledby="modal-title">
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