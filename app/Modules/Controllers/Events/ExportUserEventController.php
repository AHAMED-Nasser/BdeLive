<?php

declare(strict_types=1);

namespace App\Modules\Controllers\Events;

use App\Modules\Controllers\AdminController;
use App\Modules\Controllers\DefaultController;
use App\Modules\Repositories\EventRegistrationRepository;
use App\Modules\Repositories\EventRepository;
use App\Core\Database;
use Dompdf\Dompdf;
use Dompdf\Options;
use JetBrains\PhpStorm\NoReturn;

/**
 * Controller responsible for exporting event registration lists to PDF format.
 *
 * This controller handles the authentication check, data retrieval for a specific event,
 * and uses the Dompdf library to generate a downloadable participant list.
 *
 * Refactored to use Data Mapper pattern with Event entities.
 *
 * @package App\Modules\Controllers\Events
 * @version 2.0.0 - Data Mapper refactoring
 * @author BDELIVE - Group 8
 *
 * @see AdminController For admin authentication requirements
 * @see EventRepository For database operations
 * @see EventRegistrationRepository For database operations
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

        $eventId = isset($_POST['id']) ? (int) $_POST['id'] : 0;
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
     * Groups are separated with spacing for easy identification.
     *
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

        $eventRepository = new EventRepository(Database::getInstance()->getConnection());
        $registrationRepo = new EventRegistrationRepository();

        $event = $eventRepository->findById($eventId);

        if (!$event) {
            $this->setError("Événement introuvable");
            $this->redirect('index.php?page=event');
        }

        // Check if this is a group event
        $isGroupEvent = $event->isGroupEvent();

        // Get registrations grouped for PDF
        $groupedRegistrations = $registrationRepo->getRegisteredUsersGroupedForPdf($eventId);
        $individualRegistrations = $groupedRegistrations['individual'];
        $teamRegistrations = $groupedRegistrations['teams'];

        // Dompdf configuration
        $options = new Options();
        $options->set('defaultFont', 'Arial');
        $dompdf = new Dompdf($options);

        // Load external CSS for PDF styling
        $cssPath = __DIR__ . '/../../../assets/css/pages/pdf-export.css';
        $cssContent = file_exists($cssPath) ? (string) file_get_contents($cssPath) : '';

        // HTML build of the pdf
        ob_start();
        ?>
        <html>

        <head>
            <style>
                <?= $cssContent ?>
            </style>
        </head>

        <body>
            <h1>Liste des inscrits: <?= htmlspecialchars($event->getName()) ?></h1>
            <p class="event-info">
                Date d'exportation du PDF : <?= htmlspecialchars(date('d M Y')) ?> à <?= htmlspecialchars(date('H:i')) ?>
                <br>
                Date de l'événement : <?= htmlspecialchars(date('d M Y', (int) strtotime($event->getDate()))) ?>
                à <?= htmlspecialchars(date('H:i', (int) strtotime($event->getTime()))) ?>
                <?php if ($isGroupEvent) : ?>
                    <br>Evenement en groupe (<?= htmlspecialchars((string) $event->getTeamSize()) ?> personnes/groupe)
                <?php endif; ?>
            </p>

            <!-- Statistics -->
            <div class="stats">
                <strong>Statistiques :</strong>
                <?php
                $totalIndividual = count($individualRegistrations);
                $totalTeams = count($teamRegistrations);
                $totalTeamMembers = array_sum(array_map('count', $teamRegistrations));
                ?>
                <?php if ($totalIndividual > 0) : ?>
                    <?= $totalIndividual ?> inscription(s) individuelle(s)
                <?php endif; ?>
                <?php if ($totalTeams > 0) : ?>
                    | <?= $totalTeams ?> groupe(s) (<?= $totalTeamMembers ?> personnes)
                <?php endif; ?>
                | Total : <?= $totalIndividual + $totalTeamMembers ?> participant(s)
            </div>

            <?php if (!empty($individualRegistrations)) : ?>
                <h3>Inscriptions individuelles</h3>
                <table>
                    <colgroup>
                        <col style="width: 25%">
                        <col style="width: 25%">
                        <col style="width: 20%">
                        <col style="width: 15%">
                        <col style="width: 15%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th>Nom</th>
                            <th>Prenom</th>
                            <th>Statut</th>
                            <th class="checkbox-col">Present</th>
                            <th class="checkbox-col">Absent</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($individualRegistrations as $user) : ?>
                            <tr>
                                <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                                <td><?= htmlspecialchars($user['user_status'] ?? '') ?></td>
                                <td class="checkbox-col">
                                    <div class="box"></div>
                                </td>
                                <td class="checkbox-col">
                                    <div class="box"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <?php if (!empty($teamRegistrations)) : ?>
                <h2>Groupes inscrits</h2>

                <?php foreach ($teamRegistrations as $teamNumber => $members) : ?>
                    <table>
                        <colgroup>
                            <col style="width: 25%">
                            <col style="width: 25%">
                            <col style="width: 20%">
                            <col style="width: 15%">
                            <col style="width: 15%">
                        </colgroup>
                        <thead>
                            <tr class="group-title-row">
                                <td colspan="5">Groupe <?= htmlspecialchars((string) $teamNumber) ?></td>
                            </tr>
                            <tr>
                                <th>Nom</th>
                                <th>Prenom</th>
                                <th>Promotion</th>
                                <th class="checkbox-col">Present</th>
                                <th class="checkbox-col">Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $user) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['user_status'] ?? '') ?></td>
                                    <td class="checkbox-col">
                                        <div class="box"></div>
                                    </td>
                                    <td class="checkbox-col">
                                        <div class="box"></div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="team-separator"></div>
                <?php endforeach; ?>
            <?php endif; ?>

            <?php if (empty($individualRegistrations) && empty($teamRegistrations)) : ?>
                <p class="empty-message">
                    Aucune inscription pour cet evenement.
                </p>
            <?php endif; ?>
        </body>

        </html>

        <?php
        $html = ob_get_clean();

        // Generation
        $dompdf->loadHtml((string) $html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Send to navigator
        $dompdf->stream("inscrits_evenement_" . $event->getName() . ".pdf", ["Attachment" => true]);
        exit;
    }
}
