<?php

/**
 * Daily View HTML Template
 *
 * Expected variables:
 * - $daySchedule : array returned by DayScheduleManager::generateDaySchedule()
 * - $pageUrl : string
 * - $extraParams : array
 */

$daySchedule = $daySchedule ?? [];
$pageUrl = $pageUrl ?? 'index.php';
$extraParams = $extraParams ?? [];

// URL helper
$buildUrl = function ($date) use ($pageUrl, $extraParams) {
    $params = array_merge($extraParams, [
        'view' => 'day',
        'date' => $date
    ]);
    return $pageUrl . '&' . http_build_query($params);
};
?>

<link rel="stylesheet" href="./assets/css/weekly-schedule.css">
<link rel="stylesheet" href="./assets/css/calendar.css">
<script src="./assets/js/schedule-modal.js" defer></script>

<div class="weekly-schedule day-view" role="region" aria-label="Emploi du temps du <?= $daySchedule['formatted'] ?>">

    <!-- Toolbar Navigation Pro -->
    <div class="calendar-header">
        <!-- Left: Today Button -->
        <?php
        $todayDate = date('Y-m-d');
        $todayUrl = $pageUrl . '&view=day&date=' . $todayDate . '&' . http_build_query($extraParams);
        ?>
        <a href="<?= $todayUrl ?>" class="nav-btn today-btn" aria-label="Retour à aujourd'hui" data-view="day"
            data-date="<?= $todayDate ?>">
            Aujourd'hui
        </a>

        <!-- Center: Navigation Controls -->
        <div class="calendar-nav-center">
            <a href="<?= $buildUrl($daySchedule['prevDate']) ?>" class="nav-btn icon-btn" aria-label="Jour précédent"
                data-view="day" data-date="<?= $daySchedule['prevDate'] ?>">
                <i class="fas fa-chevron-left"></i>
            </a>

            <h2 class="calendar-title">
                <?= $daySchedule['dayName'] ?> <?= $daySchedule['formatted'] ?>
            </h2>

            <a href="<?= $buildUrl($daySchedule['nextDate']) ?>" class="nav-btn icon-btn" aria-label="Jour suivant"
                data-view="day" data-date="<?= $daySchedule['nextDate'] ?>">
                <i class="fas fa-chevron-right"></i>
            </a>
        </div>

        <!-- Right: View Switcher -->
        <div class="view-switcher">
            <a href="<?= $buildUrl($daySchedule['date']) ?>" class="view-btn active" data-view="day">
                Jour
            </a>
            <a href="<?= str_replace('view=day', 'view=week', $pageUrl) . '&' . http_build_query($extraParams) ?>"
                class="view-btn" data-view="week">
                Semaine
            </a>
            <a href="<?= str_replace('view=day', 'view=month', $pageUrl) . '&' . http_build_query($extraParams) ?>"
                class="view-btn" data-view="month">
                Mois
            </a>
        </div>
    </div>

    <!-- Conteneur Grille -->
    <div class="schedule-grid-container">

        <!-- Axe Heures -->
        <div class="time-axis">
            <?php foreach ($daySchedule['hours'] as $hour) : ?>
                <div class="time-slot"><?= htmlspecialchars($hour) ?></div>
            <?php endforeach; ?>
        </div>

        <!-- Colonne Jour Unique -->
        <div class="schedule-grid-wrapper">
            <div class="schedule-grid" style="grid-template-columns: 1fr;">

                <!-- En-tête -->
                <div class="day-header">
                    <?= htmlspecialchars($daySchedule['dayName']) ?><br>
                    <small
                        style="font-weight: 400; opacity: 0.8;"><?= htmlspecialchars($daySchedule['formatted']) ?></small>
                </div>

                <!-- Colonne -->
                <div class="day-column" data-date="<?= $daySchedule['date'] ?>">

                    <!-- Lignes horaires -->
                    <?php foreach ($daySchedule['hours'] as $index => $hour) : ?>
                        <div class="hour-line" style="top: <?= $index * 60 ?>px;"></div>
                    <?php endforeach; ?>

                    <!-- Événements -->
                    <?php if (!empty($daySchedule['events'])) : ?>
                        <?php foreach ($daySchedule['events'] as $event) :
                            $title = $event['title'] ?? 'Cours';
                            $location = $event['location'] ?? '';
                            $teacher = $event['teacher'] ?? '';
                            $type = $event['type'] ?? 'default';
                            $cssPos = $event['cssPosition'] ?? ['top' => '0px', 'height' => '60px'];

                            $startTime = isset($event['start']) ? date('H:i', strtotime($event['start'])) : '';
                            $endTime = isset($event['end']) ? date('H:i', strtotime($event['end'])) : '';
                            $timeRange = "$startTime - $endTime";
                            ?>
                            <div class="course-block" data-type="<?= htmlspecialchars($type) ?>"
                                data-time="<?= htmlspecialchars($timeRange) ?>" data-title="<?= htmlspecialchars($title) ?>"
                                data-location="<?= htmlspecialchars($location) ?>"
                                data-teacher="<?= htmlspecialchars($teacher) ?>"
                                style="top: <?= htmlspecialchars($cssPos['top']) ?>; height: <?= htmlspecialchars($cssPos['height']) ?>;"
                                tabindex="0" role="button">

                                <div class="course-time"><?= htmlspecialchars($timeRange) ?></div>
                                <div class="course-title"><?= htmlspecialchars($title) ?></div>
                                <?php if ($location) : ?>
                                    <div class="course-location">📍 <?= htmlspecialchars($location) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Indicateur temps réel -->
                    <?php
                    $ct = $daySchedule['currentTime'];
                    if ($ct) :
                        ?>
                        <div class="current-time-indicator" style="top: <?= $ct['top'] ?>px;"
                            data-time="<?= sprintf('%02d:%02d', $ct['hour'], $ct['minute']) ?>">
                            <span class="time-label"><?= sprintf('%02d:%02d', $ct['hour'], $ct['minute']) ?></span>
                        </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
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