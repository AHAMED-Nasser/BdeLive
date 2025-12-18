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

/**
 * Controller responsible for exporting event registration lists to PDF format.
 *
 * This controller handles the authentication check, data retrieval for a specific event,
 * and uses the Dompdf library to generate a downloadable participant list.
 *
 * @package App\Modules\Controllers\Events
 */
class ExportUserEventController extends AdminController
{
    /**
     * Constructor - Handles the request lifecycle.
     *
     * Validates user authentication and the presence of a valid event ID
     * before triggering the PDF generation process.
     *
     * @return void
     */
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

    /**
     * Generates and streams a PDF file containing the list of registrants.
     * Fetches event data and registrant details, renders an HTML template,
     * and sends the resulting PDF to the browser as an attachment.
     * @param int $eventId The validated event identifier.
     * @return void
     * @throws \Exception
     */
    private function generatePdf(int $eventId): void
    {
        if (!class_exists('Dompdf\Options')) {
            $autoloadPath = __DIR__ . '/../../../../vendor/autoload.php';
            if (file_exists($autoloadPath)) {
                require_once $autoloadPath;
            } else {
                throw new \Exception("L'autoloader de Composer est introuvable. Veuillez lancer 'composer install'.");
            }
        }

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
                    .checkbox-col { width: 60px; text-align: center; } /* Largeur fixe pour les cases */
                    .box { height: 15px; width: 15px; border: 1px solid #333; margin: auto; } /* Dessine le carré */
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
                            <th>Présent</th>
                            <th>Abscent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registrations as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['first_name']) ?></td>
                                <td><?= htmlspecialchars($user['user_status']) ?></td>
                                <td class="checkbox-col"><div class="box"></div></td>
                                <td class="checkbox-col"><div class="box"></div></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </body>
        </html>

        <?php
        $html = ob_get_clean();

        // Generation
        $dompdf->loadHtml((string)$html); // string for phpstan
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Send to navigator
        $dompdf->stream("inscrits_evenement_" . $event['event_name'] . ".pdf", ["Attachment" => true]);
        exit;
    }
}
