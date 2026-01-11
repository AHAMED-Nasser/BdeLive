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

    <!-- Modal pour les détails d'événement -->
    <div id="event-modal" class="event-modal" style="display: none;">
        <div class="event-modal-overlay"></div>
        <div class="event-modal-content">
            <button class="event-modal-close" aria-label="Fermer">&times;</button>
            <div class="event-modal-header">
                <h2 id="event-modal-title" aria-hidden="true">Détails de l'événement</h2>
            </div>
            <div class="event-modal-body">
                <div id="event-modal-details"></div>
            </div>
            <div class="event-modal-footer">
                <button class="event-modal-button" id="event-modal-ok">OK</button>
            </div>
        </div>
    </div>

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
                
                <!-- VUE HEBDOMADAIRE (EMPLOI DU TEMPS) -->
                <?php if (isset($weeklySchedule)) : ?>
                    <?php
                    $pageUrl = 'index.php?page=schedule';
                    $extraParams = [
                        'year' => $selectedYear,
                        'group' => $selectedGroup
                    ];
                    $schedule = $weeklySchedule;
                    include __DIR__ . '/../components/weekly-schedule.php';
                    ?>
                <?php else : ?>
                    <div style="text-align: center; padding: 3rem; color: var(--text-secondary);">
                        <p>Impossible de charger l'emploi du temps</p>
                    </div>
                <?php endif; ?>

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
            
            // Détection mobile
            const isMobile = window.innerWidth <= 767;
            
            // Configuration responsive de la toolbar
            const getHeaderToolbar = () => {
                if (isMobile) {
                    return {
                        left: 'prev,next',
                        center: 'title',
                        right: ''
                    };
                }
                return {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
                };
            };
            
            // Vue initiale responsive
            const getInitialView = () => {
                if (isMobile) {
                    return 'listWeek'; // Vue liste par défaut sur mobile
                }
                return 'timeGridWeek';
            };
            
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: getInitialView(),
                locale: 'fr',
                headerToolbar: getHeaderToolbar(),
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
                // Options responsive
                contentHeight: 'auto',
                aspectRatio: isMobile ? 1.5 : 1.8,
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
                    const modal = document.getElementById('event-modal');
                    const modalTitle = document.getElementById('event-modal-title');
                    const modalDetails = document.getElementById('event-modal-details');
                    
                    // Remplir le titre
                    modalTitle.textContent = event.title;
                    
                    // Construire les détails
                    let detailsHTML = '';
                    if (event.extendedProps.location) {
                        detailsHTML += `<div class="event-detail-item"><span class="event-detail-icon">📍</span><span class="event-detail-text">${event.extendedProps.location}</span></div>`;
                    }
                    if (event.extendedProps.teacher) {
                        detailsHTML += `<div class="event-detail-item"><span class="event-detail-icon">👨‍🏫</span><span class="event-detail-text">${event.extendedProps.teacher}</span></div>`;
                    }
                    
                    const options = { hour: '2-digit', minute: '2-digit' };
                    const timeText = `${event.start.toLocaleTimeString('fr-FR', options)} - ${event.end.toLocaleTimeString('fr-FR', options)}`;
                    detailsHTML += `<div class="event-detail-item"><span class="event-detail-icon">🕐</span><span class="event-detail-text">${timeText}</span></div>`;
                    
                    modalDetails.innerHTML = detailsHTML;
                    
                    // Afficher la modal
                    modal.style.display = 'flex';
                },
                eventContent: function(arg) {
                    const location = arg.event.extendedProps.location;
                    let html = `<div class="fc-event-main-frame">`;
                    html += `<div class="fc-event-time">${arg.timeText}</div>`;
                    html += `<div class="fc-event-title-container">`;
                    html += `<div class="fc-event-title">${arg.event.title}</div>`;
                    if (location) html += `<div class="fc-event-location">📍 ${location}</div>`;
                    html += `</div></div>`;
                    return { html: html };
                },
                eventDidMount: function(arg) {
                    // S'assurer que les couleurs sont appliquées dans la vue liste
                    if (arg.view.type === 'listWeek') {
                        const eventEl = arg.el;
                        const bgColor = arg.event.backgroundColor;
                        const borderColor = arg.event.borderColor || bgColor;
                        
                        if (bgColor && eventEl) {
                            // Appliquer la couleur sur .fc-list-event lui-même
                            eventEl.style.backgroundColor = bgColor;
                            eventEl.style.borderColor = borderColor;
                            
                            // Appliquer la couleur aux cellules td
                            const tds = eventEl.querySelectorAll('td');
                            tds.forEach(td => {
                                td.style.backgroundColor = bgColor;
                                // Ne pas forcer la couleur du texte, laisser le CSS gérer selon le mode
                            });
                            
                            // Ne pas forcer la couleur, laisser le CSS gérer selon le mode (dark/light)
                            const textElements = eventEl.querySelectorAll('.fc-event-title, .fc-event-time, .fc-list-event-time, .fc-list-event-title');
                            textElements.forEach(el => {
                                // Retir du forçage de couleur - géré par CSS
                            });
                        }
                    }
                }
            });
            
            calendar.render();
            
            // Ajouter un menu de sélection de vue sur mobile
            if (isMobile) {
                const toolbar = calendarEl.querySelector('.fc-toolbar');
                if (toolbar) {
                    const viewSelector = document.createElement('div');
                    viewSelector.className = 'fc-view-selector-mobile';
                    viewSelector.style.cssText = 'display: flex; gap: 0.5rem; margin-top: 0.5rem; justify-content: center; flex-wrap: wrap; padding: 0.5rem;';
                    
                    const views = [
                        { key: 'listWeek', label: 'Liste' },
                        { key: 'timeGridDay', label: 'Jour' },
                        { key: 'timeGridWeek', label: 'Semaine' }
                    ];
                    
                    views.forEach(view => {
                        const btn = document.createElement('button');
                        btn.className = 'fc-button fc-button-primary';
                        btn.textContent = view.label;
                        btn.style.cssText = 'padding: 0.4rem 0.8rem; font-size: 0.8rem; border-radius: 4px; cursor: pointer;';
                        
                        if (calendar.view.type === view.key) {
                            btn.classList.add('fc-button-active');
                        }
                        
                        btn.addEventListener('click', () => {
                            calendar.changeView(view.key);
                            viewSelector.querySelectorAll('button').forEach(b => b.classList.remove('fc-button-active'));
                            btn.classList.add('fc-button-active');
                        });
                        
                        viewSelector.appendChild(btn);
                    });
                    
                    toolbar.appendChild(viewSelector);
                }
            }
            
            // Adapter la toolbar lors du redimensionnement
            let resizeTimer;
            window.addEventListener('resize', () => {
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    const newIsMobile = window.innerWidth <= 767;
                    if (newIsMobile !== isMobile) {
                        location.reload(); // Recharger pour appliquer la nouvelle configuration
                    }
                }, 250);
            });
            
            <?php endif; ?>
        });
        
        // Gestion de la modal d'événement
        const eventModal = document.getElementById('event-modal');
        const eventModalClose = document.querySelector('.event-modal-close');
        const eventModalOk = document.getElementById('event-modal-ok');
        const eventModalOverlay = document.querySelector('.event-modal-overlay');
        
        function closeEventModal() {
            eventModal.style.display = 'none';
        }
        
        if (eventModalClose) {
            eventModalClose.addEventListener('click', closeEventModal);
        }
        
        if (eventModalOk) {
            eventModalOk.addEventListener('click', closeEventModal);
        }
        
        if (eventModalOverlay) {
            eventModalOverlay.addEventListener('click', closeEventModal);
        }
        
        // Fermer avec la touche Escape
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && eventModal.style.display === 'flex') {
                closeEventModal();
            }
        });
    </script>

<?php end_page(); ?>