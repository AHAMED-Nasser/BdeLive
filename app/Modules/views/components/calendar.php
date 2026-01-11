<?php
/**
 * Template HTML pour le calendrier natif
 * 
 * Variables attendues :
 * - $calendar : array retourné par CalendarManager::generateMonthCalendar()
 * - $pageUrl : string - URL de base (ex: 'index.php?page=schedule')
 * - $extraParams : array - Paramètres URL supplémentaires (ex: ['group' => 'GA2-2'])
 */

//Valeurs par défaut si non définies
$calendar = $calendar ?? ['days' => [], 'monthName' => '', 'year' => date('Y'), 'month' => (int)date('n'), 'prevYear' => (int)date('Y'), 'prevMonth' => (int)date('n')-1, 'nextYear' => (int)date('Y'), 'nextMonth' => (int)date('n')+1];
$pageUrl = $pageUrl ?? 'index.php';
$extraParams = $extraParams ?? [];

// Construction de l'URL avec paramètres (IMPORTANT: utiliser calyear/calmonth pour éviter collision avec year du groupe)
$buildUrl = function($year, $month) use ($pageUrl, $extraParams) {
    $params = array_merge($extraParams, [
        'calyear' => $year,
        'calmonth' => $month
    ]);
    return $pageUrl . '&' . http_build_query($params);
};
?>

<link rel="stylesheet" href="/assets/css/calendar.css">

<div class="calendar-container" role="region" aria-label="Calendrier <?= htmlspecialchars($calendar['monthName']) ?> <?= $calendar['year'] ?>">
    
    <!-- Boutons de Vue -->
    <div class="view-switcher">
        <a href="<?= $pageUrl ?>&view=day&date=<?= date('Y-m-d') ?>&<?= http_build_query($extraParams) ?>" 
           class="view-btn">
            📅 Jour
        </a>
        <a href="<?= $pageUrl ?>&view=week&<?= http_build_query($extraParams) ?>" 
           class="view-btn">
            📆 Semaine
        </a>
        <a href="<?= $pageUrl ?>&view=month&<?= http_build_query($extraParams) ?>" 
           class="view-btn active">
            🗓️ Mois
        </a>
    </div>

    <!-- En-tête Navigation -->
    <div class="calendar-header">
        <a href="<?= $buildUrl($calendar['prevYear'], $calendar['prevMonth']) ?>" 
           class="calendar-nav-btn"
           aria-label="Mois précédent : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['prevMonth'])) ?> <?= $calendar['prevYear'] ?>">
            ← Précédent
        </a>
        
        <h2 class="calendar-title">
            <?= htmlspecialchars($calendar['monthName']) ?> <?= $calendar['year'] ?>
        </h2>
        
        <a href="<?= $buildUrl($calendar['nextYear'], $calendar['nextMonth']) ?>" 
           class="calendar-nav-btn"
           aria-label="Mois suivant : <?= htmlspecialchars((new \App\Core\CalendarManager())->getMonthName($calendar['nextMonth'])) ?> <?= $calendar['nextYear'] ?>">
            Suivant →
        </a>
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
        <?php foreach ($calendar['days'] as $day): ?>
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
            
            <div class="<?= implode(' ', $dayClasses) ?>" 
                 data-date="<?= htmlspecialchars($day['date']) ?>"
                 aria-label="<?= htmlspecialchars($dayLabel) ?>">
                
                <div class="calendar-day-number"><?= $day['number'] ?></div>
                
                <?php if (!empty($day['events'])): ?>
                    <div class="calendar-events">
                        <?php foreach ($day['events'] as $event): ?>
                            <?php
                            // Déterminer la couleur de l'événement
                            $eventColor = $event['color'] ?? $event['backgroundColor'] ?? '#3788d8';
                            $eventType = $event['type'] ?? '';
                            
                            // Extraire l'heure si disponible
                            $eventTime = '';
                            if (isset($event['event_time'])) {
                                $eventTime = date('H:i', strtotime($event['event_time']));
                            } elseif (isset($event['start'])) {
                                $eventTime = date('H:i', strtotime($event['start']));
                            }
                            
                            // Titre de l'événement
                            $eventTitle = $event['event_name'] ?? $event['title'] ?? 'Événement';
                            
                            // URL de l'événement
                            $eventUrl = '#';
                            if (isset($event['event_id'])) {
                                $eventUrl = 'index.php?page=showEvent&id=' . $event['event_id'];
                            } elseif (isset($event['id'])) {
                                $eventUrl = 'index.php?page=showEvent&id=' . $event['id'];
                            }
                            
                            // ARIA label complet
                            $eventAriaLabel = $eventTitle;
                            if ($eventTime) {
                                $eventAriaLabel .= ' à ' . $eventTime;
                            }
                            if (isset($event['location'])) {
                                $eventAriaLabel .= ', lieu : ' . $event['location'];
                            }
                            ?>
                            
                            <a href="<?= htmlspecialchars($eventUrl) ?>" 
                               class="calendar-event"
                               <?= $eventType ? 'data-type="' . htmlspecialchars($eventType) . '"' : '' ?>
                               style="background-color: <?= htmlspecialchars($eventColor) ?>;"
                               aria-label="<?= htmlspecialchars($eventAriaLabel) ?>">
                                <?php if ($eventTime): ?>
                                    <span class="event-time"><?= htmlspecialchars($eventTime) ?></span>
                                <?php endif; ?>
                                <span class="event-title"><?= htmlspecialchars($eventTitle) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
