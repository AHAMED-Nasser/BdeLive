<?php
start_page('Evénements');

// Images par défaut pour les événements (en attendant les images en BDD)
$defaultImages = [
    ['src' => './assets/img/carousel/events/event2.jpg'],
    ['src' => './assets/img/carousel/events/event1.svg'],
];
?>

<section class="events">
    <h1 class="title">Événements</h1>
    
    <?php if (empty($paginationData['items'])): ?>
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
                $eventDetails .= ' | ' . htmlspecialchars(mb_strlen($desc) > 100 ? mb_substr($desc, 0, 100) . '...' : $desc);
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
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
    
    <?php if ($paginationData['totalPages'] > 1):
        $current = $paginationData['currentPage'];
        $total = $paginationData['totalPages'];
        $start = max(1, $current - 2);
        $end = min($total, $current + 2);
        ?>
        <nav aria-label="pagination">
            <p class="pagination-info">
                Page <?= $current ?> sur <?= $total ?> (<?= $paginationData['totalItems'] ?> événement<?= $paginationData['totalItems'] > 1 ? 's' : '' ?>)
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