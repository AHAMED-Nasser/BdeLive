<?php
/**
 * Vue de la page emploi du temps
 *
 * @var array<string, array<string, mixed>> $groups
 * @var string $selectedYear
 * @var string $selectedGroup
 * @var array<string, mixed>|null $user
 * @var array<string, string|null> $flash
 */

start_page("Emploi du temps - BDE Inform'Aix", true, $user ?? null);
?>

<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css' />
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/locales/fr.global.min.js'></script>

<style>
    .schedule-container {
        max-width: 1400px;
        margin: 2rem auto;
        padding: 0 1rem;
    }

    .schedule-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 2rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .schedule-header h1 {
        margin: 0 0 0.5rem 0;
        font-size: 2rem;
    }

    .schedule-header p {
        margin: 0;
        opacity: 0.9;
    }

    .schedule-filters {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .filter-group {
        margin-bottom: 1rem;
    }

    .filter-group:last-child {
        margin-bottom: 0;
    }

    .filter-group label {
        display: block;
        font-weight: 600;
        margin-bottom: 0.5rem;
        color: #333;
    }

    .filter-group select {
        width: 100%;
        padding: 0.75rem;
        border: 2px solid #e0e0e0;
        border-radius: 8px;
        font-size: 1rem;
        transition: border-color 0.3s;
    }

    .filter-group select:focus {
        outline: none;
        border-color: #667eea;
    }

    .calendar-wrapper {
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    #calendar {
        max-width: 100%;
    }

    /* Personnalisation FullCalendar */
    .fc-toolbar-title {
        font-size: 1.5rem !important;
        font-weight: 600 !important;
    }

    .fc-button {
        background-color: #667eea !important;
        border-color: #667eea !important;
        text-transform: capitalize !important;
    }

    .fc-button:hover {
        background-color: #5568d3 !important;
        border-color: #5568d3 !important;
    }

    .fc-button:disabled {
        opacity: 0.5 !important;
    }

    .fc-event {
        cursor: pointer;
        border-radius: 4px;
        padding: 2px 4px;
    }

    .fc-daygrid-event {
        margin: 2px 0;
    }

    .no-group-selected {
        text-align: center;
        padding: 3rem;
        color: #666;
    }

    .no-group-selected i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ccc;
    }

    /* Legend */
    .calendar-legend {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        margin-top: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }

    .legend-label {
        font-size: 0.9rem;
        color: #555;
    }

    /* Responsive */
    @media (min-width: 768px) {
        .schedule-filters {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }

        .filter-group {
            margin-bottom: 0;
        }
    }

    @media (max-width: 768px) {
        .schedule-header h1 {
            font-size: 1.5rem;
        }

        .calendar-legend {
            flex-direction: column;
            gap: 0.5rem;
        }
    }
</style>

<main class="schedule-container">
    <?php if (!empty($flash['error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <div class="schedule-header">
        <h1>📅 Emploi du temps</h1>
        <p>Consultez votre emploi du temps par groupe</p>
    </div>

    <div class="schedule-filters">
        <div class="filter-group">
            <label for="year-select">Année :</label>
            <select id="year-select" name="year">
                <option value="">-- Sélectionner une année --</option>
                <?php foreach ($groups as $yearKey => $yearData): ?>
                    <option value="<?= htmlspecialchars($yearKey) ?>"
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
                <?php if ($selectedYear && isset($groups[$selectedYear])): ?>
                    <?php foreach ($groups[$selectedYear]['groups'] as $groupKey => $groupLabel): ?>
                        <option value="<?= htmlspecialchars($groupKey) ?>"
                                <?= $selectedGroup === $groupKey ? 'selected' : '' ?>>
                            <?= htmlspecialchars($groupLabel) ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
        </div>
    </div>

    <div class="calendar-wrapper">
        <?php if ($selectedGroup): ?>
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
    document.addEventListener('DOMContentLoaded', function() {
        const yearSelect = document.getElementById('year-select');
        const groupSelect = document.getElementById('group-select');

        // Groupes disponibles par année (avec labels)
        const groupsByYear = <?= json_encode(array_map(fn($y) => $y['groups'], $groups)) ?>;

        // Mettre à jour les groupes disponibles selon l'année sélectionnée
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

            // Recharger la page avec la nouvelle année
            updateUrl();
        });

        // Recharger la page quand le groupe change
        groupSelect.addEventListener('change', function() {
            updateUrl();
        });

        function updateUrl() {
            const year = yearSelect.value;
            const group = groupSelect.value;

            if (year) {
                const params = new URLSearchParams();
                params.set('page', 'schedule');
                params.set('year', year);
                if (group) {
                    params.set('group', group);
                }
                window.location.href = 'index.php?' + params.toString();
            }
        }

        <?php if ($selectedGroup): ?>
        // Initialiser FullCalendar
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
                list: 'Liste'
            },
            slotMinTime: '07:00:00',
            slotMaxTime: '20:00:00',
            weekends: false,
            allDaySlot: false,
            height: 'auto',
            events: function(info, successCallback, failureCallback) {
                fetch('index.php?page=schedule&action=get-events&year=<?= htmlspecialchars($selectedYear) ?>&group=<?= htmlspecialchars($selectedGroup) ?>')
                    .then(response => response.json())
                    .then(data => {
                        if (data.error) {
                            console.error('Erreur:', data.error);
                            failureCallback(data.error);
                        } else {
                            successCallback(data);
                        }
                    })
                    .catch(error => {
                        console.error('Erreur de chargement:', error);
                        failureCallback(error);
                    });
            },
            eventClick: function(info) {
                const event = info.event;
                let content = '<strong>' + event.title + '</strong><br>';

                if (event.extendedProps.location) {
                    content += '📍 ' + event.extendedProps.location + '<br>';
                }

                if (event.extendedProps.teacher) {
                    content += '👨‍🏫 ' + event.extendedProps.teacher + '<br>';
                }

                const start = event.start;
                const end = event.end;
                if (start && end) {
                    const options = { hour: '2-digit', minute: '2-digit' };
                    content += '🕐 ' + start.toLocaleTimeString('fr-FR', options) +
                        ' - ' + end.toLocaleTimeString('fr-FR', options);
                }

                alert(content);
            },
            eventContent: function(arg) {
                const timeText = arg.timeText;
                const title = arg.event.title;
                const location = arg.event.extendedProps.location;

                let html = '<div class="fc-event-main-frame">';
                html += '<div class="fc-event-time">' + timeText + '</div>';
                html += '<div class="fc-event-title-container">';
                html += '<div class="fc-event-title">' + title + '</div>';
                if (location) {
                    html += '<div style="font-size: 0.85em; opacity: 0.9;">📍 ' + location + '</div>';
                }
                html += '</div>';
                html += '</div>';

                return { html: html };
            }
        });

        calendar.render();
        <?php endif; ?>
    });
</script>

<?php end_page(); ?>
