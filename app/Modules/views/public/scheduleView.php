<?php
/**
 * Schedule View
 *
 * Displays the class schedule with support for day, week, and month views.
 * Allows filtering by year level and group.
 *
 * @package BdeLive\Views\Public
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var array<string, array{name: string, groups: array<string, string>}> $groups
 * @var string $selectedYear
 * @var string $selectedGroup
 */

start_page("Emploi du temps - BDELive", true, $user ?? null);
?>

<script src="/assets/js/schedule-ajax.js" defer></script>
<script src="/assets/js/schedule-filters.js" defer></script>

<main class="schedule-container">
    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <div class="schedule-header">
        <h1>Emploi du temps</h1>
        <p>Consultez votre emploi du temps par groupe</p>
    </div>

    <div class="schedule-filters">
        <div class="filter-group">
            <label for="year-select">Année :</label>
            <select id="year-select" name="year">
                <option value="">-- Sélectionner une année --</option>
                <?php foreach ($groups as $yearKey => $yearData): ?>
                    <option value="<?= htmlspecialchars((string) $yearKey) ?>" <?= $selectedYear === $yearKey ? 'selected' : '' ?>>
                        <?= htmlspecialchars($yearData['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="filter-group">
            <label for="group-select">Groupe :</label>
            <select id="group-select" name="group">
                <option value="">-- Sélectionner un groupe --</option>
                <?php if ($selectedYear && isset($groups[$selectedYear])): ?>
                    <?php foreach ($groups[$selectedYear]['groups'] as $groupKey => $groupLabel): ?>
                        <option value="<?= htmlspecialchars((string) $groupKey) ?>" <?= $selectedGroup === $groupKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($groupLabel) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="calendar-wrapper" id="calendar-view">
        <?php if ($selectedGroup): ?>
            <!-- SWITCH DES VUES (JOUR / SEMAINE / MOIS) -->
            <?php
            $pageUrl = 'index.php?page=schedule';
            $extraParams = [
                'year' => $selectedYear,
                'group' => $selectedGroup
            ];

            // Déterminer la vue active
            $activeView = $view ?? 'week';

            if ($activeView === 'day' && isset($daySchedule)) {
                // VUE JOUR
                include __DIR__ . '/../components/day-schedule.php';
            } elseif ($activeView === 'month' && isset($nativeCalendar)) {
                // VUE MOIS
                $calendar = $nativeCalendar;
                include __DIR__ . '/../components/calendar.php';
            } elseif (isset($weeklySchedule)) {
                // VUE SEMAINE (Défaut)
                $schedule = $weeklySchedule;
                include __DIR__ . '/../components/weekly-schedule.php';
            } else {
                echo '<div style="text-align: center; padding: 3rem; color: var(--text-secondary);"><p>Impossible de charger l\'emploi du temps</p></div>';
            }
            ?>

            <div class="calendar-legend">
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #2563eb;"></div>
                    <span class="legend-label">TD</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #15803d;"></div>
                    <span class="legend-label">TP</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #b91c1c;"></div>
                    <span class="legend-label">CM</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #d97706;"></div>
                    <span class="legend-label">Examen</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #5b21b6;"></div>
                    <span class="legend-label">Soutenance</span>
                </div>
                <div class="legend-item">
                    <div class="legend-color" style="background-color: #0e7490;"></div>
                    <span class="legend-label">Support/Autonomie</span>
                </div>
            </div>
        <?php else: ?>
            <div class="no-group-selected">
                <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                <h3>Aucun groupe sélectionné</h3>
                <p>Veuillez sélectionner une année et un groupe pour afficher l'emploi du temps</p>
            </div>
        <?php endif; ?>
    </div>
</main>

<script>
    // Injecter les données des groupes pour le fichier schedule-filters.js
    window.scheduleGroupsData = <?= json_encode(array_map(fn($y) => $y['groups'], $groups)) ?>;
</script>

<?php end_page(); ?>