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
    'week' => (int)date('W'),
    'prevYear' => (int)date('Y'),
    'prevWeek' => (int)date('W') - 1,
    'nextYear' => (int)date('Y'),
    'nextWeek' => (int)date('W') + 1
];
$pageUrl = $pageUrl ?? 'index.php';
$extraParams = $extraParams ?? [];

// Construction URL navigation
$buildUrl = function($year, $week) use ($pageUrl, $extraParams) {
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
?>

<link rel="stylesheet" href="/assets/css/weekly-schedule.css">

<div class="weekly-schedule" role="region" aria-label="Emploi du temps semaine <?= $schedule['week'] ?>">
    
    <!-- Navigation Semaine -->
    <div class="schedule-nav">
        <a href="<?= $buildUrl($schedule['prevYear'], $schedule['prevWeek']) ?>" 
           class="week-nav-btn"
           aria-label="Semaine précédente">
            ← Précédent
        </a>
        
        <h2>
            Semaine <?= $schedule['week'] ?><br>
            <small style="font-size: 0.8em; font-weight: 400; opacity: 0.8;">
                <?= $weekPeriod ?>, <?= $schedule['year'] ?>
            </small>
        </h2>
        
        <a href="<?= $buildUrl($schedule['nextYear'], $schedule['nextWeek']) ?>" 
           class="week-nav-btn"
           aria-label="Semaine suivante">
            Suivant →
        </a>
    </div>
    
    <!-- Conteneur Grille -->
    <div class="schedule-grid-container">
        
        <!-- Axe des heures (gauche) -->
        <div class="time-axis" aria-label="Heures">
            <?php foreach ($schedule['hours'] as $hour): ?>
                <div class="time-slot"><?= htmlspecialchars($hour) ?></div>
            <?php endforeach; ?>
        </div>
        
        <!-- Grille des jours -->
        <div class="schedule-grid-wrapper">
            <div class="schedule-grid">
                
                <!-- En-têtes des jours -->
                <?php foreach ($schedule['weekDates'] as $dayInfo): ?>
                    <div class="day-header">
                        <?= htmlspecialchars($dayInfo['dayName']) ?><br>
                        <small style="font-weight: 400; opacity: 0.8;"><?= htmlspecialchars($dayInfo['formatted']) ?></small>
                    </div>
                <?php endforeach; ?>
                
                <!-- Colonnes des jours avec événements -->
                <?php foreach ($schedule['weekDates'] as $dayInfo): ?>
                    <div class="day-column" data-date="<?= htmlspecialchars($dayInfo['date']) ?>">
                        
                        <!-- Lignes horaires (background) -->
                        <?php foreach ($schedule['hours'] as $index => $hour): ?>
                            <div class="hour-line" style="top: <?= $index * 60 ?>px;"></div>
                        <?php endforeach; ?>
                        
                        <!-- Événements/Cours du jour -->
                        <?php 
                        $dayEvents = $schedule['events'][$dayInfo['date']] ?? [];
                        if (!empty($dayEvents)):
                            foreach ($dayEvents as $event):
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
                                if ($timeRange) $ariaLabel .= ", $timeRange";
                                if ($location) $ariaLabel .= ", $location";
                                if ($teacher) $ariaLabel .= ", $teacher";
                        ?>
                            <div class="course-block"
                                 data-type="<?= htmlspecialchars($type) ?>"
                                 style="top: <?= htmlspecialchars($cssPos['top']) ?>; height: <?= htmlspecialchars($cssPos['height']) ?>;"
                                 tabindex="0"
                                 role="button"
                                 aria-label="<?= htmlspecialchars($ariaLabel) ?>">
                                
                                <?php if ($timeRange): ?>
                                    <div class="course-time"><?= htmlspecialchars($timeRange) ?></div>
                                <?php endif; ?>
                                
                                <div class="course-title"><?= htmlspecialchars($title) ?></div>
                                
                                <?php if ($location): ?>
                                    <div class="course-location">📍 <?= htmlspecialchars($location) ?></div>
                                <?php endif; ?>
                                
                                <?php if ($teacher): ?>
                                    <div class="course-teacher">👨‍🏫 <?= htmlspecialchars($teacher) ?></div>
                                <?php endif; ?>
                            </div>
                        <?php 
                            endforeach;
                        endif;
                        ?>
                    </div>
                <?php endforeach; ?>
                
            </div>
        </div>
        
    </div>
    
</div>
