<?php
start_page('Evénements');

// Images par défaut pour les événements (en attendant les images en BDD)
$defaultImages = [
    ['src' => './assets/img/carousel/events/event2.jpg'],
    ['src' => './assets/img/carousel/events/event1.svg'],
];

// Ensure pagination data is available (set by controller)
$paginationData = $paginationData ?? [
    'items' => [],
    'currentPage' => 1,
    'totalPages' => 0,
    'totalItems' => 0
];
?>

<section class="events">
    <h1 class="title">Événements</h1>
    
    <!-- Messages flash -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 12px; margin: 20px 0; border: 1px solid #c3e6cb; border-radius: 4px;">
            <?= htmlspecialchars($_SESSION['success']) ?>
            <?php unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 12px; margin: 20px 0; border: 1px solid #f5c6cb; border-radius: 4px;">
            <?= htmlspecialchars($_SESSION['error']) ?>
            <?php unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($paginationData['items'] ?? [])): ?>
        <p style="text-align: center; padding: 40px; color: #666;">Aucun événement trouvé.</p>
    <?php else: ?>
        <?php foreach ($paginationData['items'] as $index => $event): ?>
            <?php
            // Construire le titre de l'événement avec les détails
            $eventTitle = htmlspecialchars($event['event_name']);
            $eventDetails = date('d/m/Y', strtotime($event['event_date'])) . ' à ' .
                          date('H:i', strtotime($event['event_time'])) . ' - ' .
                          htmlspecialchars($event['event_location']);

            if ($desc = $event['description'] ?? null) {
                $eventDetails .= ' | ' . htmlspecialchars(strlen($desc) > 100 ? substr($desc, 0, 100) . '...' : $desc);
            }
            ?>
            
            <div style="margin-bottom: 30px;">
                <h2 class="event-title"><?= $eventTitle ?></h2>
                <p style="text-align: center; color: #666; margin-bottom: 20px;">
                    <?= $eventDetails ?>
                    <?php if ($event['max_capacity'] ?? null): ?>
                        <br><strong>Places disponibles :</strong> <?= $event['max_capacity'] ?>
                    <?php endif; ?>
                </p>
                <?php useCarousel($eventTitle, $defaultImages, 'carousel-event-' . $event['event_id']) ?>

                <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'BDE'): ?>
                    <div style="text-align: center; margin-top: 15px;">
                        <form method="post" action="index.php?page=deleteEvent"
                              onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer l\'événement \'<?= htmlspecialchars($event['event_name']) ?>\' ?\n\nCette action est irréversible.');"
                              style="display: inline;">
                            <input type="hidden" name="event_id" value="<?= $event['event_id'] ?>">
                            <?= csrfField() ?>
                            <button type="submit"
                                    style="background-color: #dc3545; color: white; border: none; padding: 8px 16px; border-radius: 4px; cursor: pointer;">
                                Supprimer
                            </button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['user_status']) && $_SESSION['user_status'] === 'BDE'): ?>
                    <div class="event-admin-actions">
                        <a href="index.php?page=modifyEvent&event_id=<?= $event['event_id'] ?>" class="btn-modify-event">
                            ✏️ Modifier cet événement
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if (($paginationData['totalPages'] ?? 0) > 1):
        $current = $paginationData['currentPage'] ?? 1;
        $total = $paginationData['totalPages'] ?? 0;
        $start = max(1, $current - 2);
        $end = min($total, $current + 2);
        ?>
        <nav aria-label="pagination">
            <p class="pagination-info">
                Page <?= $current ?> sur <?= $total ?> (<?= $paginationData['totalItems'] ?? 0 ?> événement<?= ($paginationData['totalItems'] ?? 0) > 1 ? 's' : '' ?>)
            </p>
            <ul class="pagination">
                <?php if ($current > 1): ?>
                    <li>
                        <a href="?page=pagination&p=<?= $current - 1 ?>">
                            <span aria-hidden="true">&laquo;</span>
                            <span class="visuallyhidden">page précédente</span>
                        </a>
                    </li>
                <?php endif; ?>
                
                <?php for ($i = $start; $i <= $end; $i++): ?>
                    <li>
                        <a href="?page=pagination&p=<?= $i ?>" <?= $i === $current ? 'aria-current="page"' : '' ?>>
                            <span class="visuallyhidden">page </span><?= $i ?>
                        </a>
                    </li>
                <?php endfor; ?>
                
                <?php if ($current < $total): ?>
                    <li>
                        <a href="?page=pagination&p=<?= $current + 1 ?>">
                            <span class="visuallyhidden">page suivante</span>
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    <?php endif; ?>
</section>

<?php
end_page();
?>