<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventRepository;
use Dompdf\Dompdf;
use Dompdf\Options;
use JetBrains\PhpStorm\NoReturn;

class ExportUserEventController extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        $eventId = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($eventId <= 0) {
            $this->setError("Événement invalide.");
            $this->redirect('index.php?page=event');
        }

        $this->generatePdf($eventId);
    }

    private function generatePdf(int $eventId): void
    {
        $eventRepo = new EventRepository();
        $registrationRepo = new EventRegistrationRepository();

        $event = $eventRepo -> findById($eventId);
        $registrations = $registrationRepo -> getRegisteredUsersDetails($eventId);

        if (!$event) {
            $this->setError("Événement introuvable");
            $this->redirect('index.php?page=event');
        }

        // Dompdf configuration
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // HTML build of the pdf
        ob_start();
        ?>
        <html>
            <head>
                <style>
                    body { font-family: sans-serif}
                    table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                    th, td { border: 1px solid #dddddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    h1 { color: #333; }
                </style>
            </head>
            <body>
                <h1>Liste des inscrits: <?= htmlspecialchars($event['event_name']) ?></h1>
                <p>Date de l'événement : <?= htmlspecialchars($event['event_date']) ?> à <?= htmlspecialchars($event['event_time'])?></p>
                <table>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['user_status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </body>
        </html>

        <?php
        $html = ob_get_clean();

        // Generation
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Send to navigator
        $dompdf->stream("inscrits_evenement_" . $event['event_name'] . ".pdf", ["Attachment" => true]);
        exit;
    }
}
