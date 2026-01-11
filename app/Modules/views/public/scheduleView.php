<?php
/**
 * Vue de la page emploi du temps
 * @var array<string, array{name: string, groups: array<string, string>}> $groups
 * @var string $selectedYear
 * @var string $selectedGroup
 */

start_page("Emploi du temps - BDE Inform'Aix", true, $user ?? null);
?>

    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' />
    <link rel="stylesheet" href="/assets/css/schedule.css">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js'></script>

    <main class="schedule-container">
        <?php if (!empty($flash['error'])) : ?>
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
                    <?php foreach ($groups as $yearKey => $yearData) : ?>
                        <option value="<?= htmlspecialchars((string)$yearKey) ?>"
                                <?= $selectedYear === $yearKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($yearData['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label for="group-select">Groupe :</label>
                <select id="group-select" name="group">
                    <option value="">-- Sélectionner un groupe --</option>
                    <?php if ($selectedYear && isset($groups[$selectedYear])) : ?>
                        <?php foreach ($groups[$selectedYear]['groups'] as $groupKey => $groupLabel) : ?>
                            <option value="<?= htmlspecialchars((string)$groupKey) ?>"
                                    <?= $selectedGroup === $groupKey ? 'selected' : '' ?>>
                                <?= htmlspecialchars($groupLabel) ?>
                            </option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
            </div>
        </div>

        <div class="calendar-wrapper">
            <?php if ($selectedGroup) : ?>
                <div id='calendar'></div>

                <div class="calendar-legend">
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #3788d8;"></div>
                        <span class="legend-label">TD</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #28a745;"></div>
                        <span class="legend-label">TP</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #dc3545;"></div>
                        <span class="legend-label">CM</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #fd7e14;"></div>
                        <span class="legend-label">Examen</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #6f42c1;"></div>
                        <span class="legend-label">Soutenance</span>
                    </div>
                    <div class="legend-item">
                        <div class="legend-color" style="background-color: #17a2b8;"></div>
                        <span class="legend-label">Support/Autonomie</span>
                    </div>
                </div>
            <?php else : ?>
                <div class="no-group-selected">
                    <div style="font-size: 4rem; margin-bottom: 1rem;">📚</div>
                    <h3>Aucun groupe sélectionné</h3>
                    <p>Veuillez sélectionner une année et un groupe pour afficher l'emploi du temps</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const yearSelect = document.getElementById('year-select');
            const groupSelect = document.getElementById('group-select');

            // Groupes disponibles par année (avec labels)
            const groupsByYear = <?= json_encode(array_map(fn($y) => $y['groups'], $groups)) ?>;

            yearSelect.addEventListener('change', function() {
                const selectedYear = this.value;
                groupSelect.innerHTML = '<option value="">-- Sélectionner un groupe --</option>';

                if (selectedYear && groupsByYear[selectedYear]) {
                    Object.entries(groupsByYear[selectedYear]).forEach(function([key, label]) {
                        const option = document.createElement('option');
                        option.value = key;
                        option.textContent = label;
                        groupSelect.appendChild(option);
                    });
                }
                updateUrl();
            });

            groupSelect.addEventListener('change', updateUrl);

            function updateUrl() {
                const year = yearSelect.value;
                const group = groupSelect.value;
                if (year) {
                    const params = new URLSearchParams();
                    params.set('page', 'schedule');
                    params.set('year', year);
                    if (group) params.set('group', group);
                    window.location.href = 'index.php?' + params.toString();
                }
            }

            <?php if ($selectedGroup) : ?>
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                locale: 'fr',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
                },
                buttonText: {
                    today: "Aujourd'hui",
                    week: 'Semaine',
                    day: 'Jour',
                    list: 'Liste',
                    prev: "Précédent",
                    next: "Suivant"
                },
                slotMinTime: '07:00:00',
                slotMaxTime: '20:00:00',
                weekends: false,
                allDaySlot: false,
                height: 'auto',
                events: function(info, successCallback, failureCallback) {
                    fetch(`index.php?page=schedule&action=get-events&year=<?= urlencode($selectedYear) ?>&group=<?= urlencode($selectedGroup) ?>`)
                        .then(response => response.json())
                        .then(data => {
                            if (data.error) failureCallback(data.error);
                            else successCallback(data);
                        })
                        .catch(error => failureCallback(error));
                },
                eventClick: function(info) {
                    const event = info.event;
                    let content = `<strong>${event.title}</strong>\n`;
                    if (event.extendedProps.location) content += `📍 ${event.extendedProps.location}\n`;
                    if (event.extendedProps.teacher) content += `👨‍🏫 ${event.extendedProps.teacher}\n`;

                    const options = { hour: '2-digit', minute: '2-digit' };
                    content += `🕐 ${event.start.toLocaleTimeString('fr-FR', options)} - ${event.end.toLocaleTimeString('fr-FR', options)}`;

                    alert(content);
                },
                eventContent: function(arg) {
                    const location = arg.event.extendedProps.location;
                    let html = `<div class="fc-event-main-frame">`;
                    html += `<div class="fc-event-time">${arg.timeText}</div>`;
                    html += `<div class="fc-event-title-container">`;
                    html += `<div class="fc-event-title">${arg.event.title}</div>`;
                    if (location) html += `<div style="font-size: 0.85em; opacity: 0.9;">📍 ${location}</div>`;
                    html += `</div></div>`;
                    return { html: html };
                }
            });
            calendar.render();
            <?php endif; ?>
        });
    </script>

<?php end_page(); ?>
