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

        $eventRepo = new EventRepository();
        $registrationRepo = new EventRegistrationRepository();

        $event = $eventRepo -> findById($eventId);

        if (!$event) {
            $this->setError("Événement introuvable");
            $this->redirect('index.php?page=event');
        }

        // Check if this is a group event
        $isGroupEvent = !empty($event['is_group_event']) && $event['is_group_event'] == 1;

        // Get registrations grouped for PDF
        $groupedRegistrations = $registrationRepo->getRegisteredUsersGroupedForPdf($eventId);
        $individualRegistrations = $groupedRegistrations['individual'];
        $teamRegistrations = $groupedRegistrations['teams'];

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
                    body { font-family: sans-serif; }
                    table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                    th, td { border: 1px solid #dddddd; padding: 8px; text-align: left; }
                    th { background-color: #f2f2f2; }
                    h1 { color: #333; margin-bottom: 5px; }
                    h2 { color: #667eea; margin-top: 30px; margin-bottom: 10px; padding: 10px; background: #f0f2ff; border-left: 4px solid #667eea; }
                    h3 { color: #333; margin-top: 25px; margin-bottom: 10px; }
                    .checkbox-col { width: 60px; text-align: center; }
                    .box { height: 15px; width: 15px; border: 1px solid #333; margin: auto; }
                    .team-separator { height: 20px; }
                    .event-info { color: #666; margin-bottom: 20px; }
                    .team-header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 12px; border-radius: 6px; margin-top: 25px; margin-bottom: 10px; }
                    .stats { background: #f8f9fa; padding: 15px; border-radius: 8px; margin: 20px 0; }
                </style>
            </head>
            <body>
                <h1>Liste des inscrits: <?= htmlspecialchars($event['event_name']) ?></h1>
                <p class="event-info">
                    📅 Date : <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?>
                    à <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?>
                    <?php if ($isGroupEvent) : ?>
                        <br>👥 Événement en groupe (<?= htmlspecialchars((string)($event['team_size'] ?? 1)) ?> personnes/groupe)
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
                        📝 <?= $totalIndividual ?> inscription(s) individuelle(s)
                    <?php endif; ?>
                    <?php if ($totalTeams > 0) : ?>
                        | 🏆 <?= $totalTeams ?> groupe(s) (<?= $totalTeamMembers ?> personnes)
                    <?php endif; ?>
                    | 📊 Total : <?= $totalIndividual + $totalTeamMembers ?> participant(s)
                </div>

                <?php if (!empty($individualRegistrations)) : ?>
                    <h3>📝 Inscriptions individuelles</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Statut</th>
                                <th class="checkbox-col">Présent</th>
                                <th class="checkbox-col">Absent</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($individualRegistrations as $user) : ?>
                                <tr>
                                    <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                                    <td><?= htmlspecialchars($user['user_status'] ?? '') ?></td>
                                    <td class="checkbox-col"><div class="box"></div></td>
                                    <td class="checkbox-col"><div class="box"></div></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <?php if (!empty($teamRegistrations)) : ?>
                    <h2>🏆 Groupes inscrits</h2>
                    
                    <?php foreach ($teamRegistrations as $teamNumber => $members) : ?>
                        <div class="team-header">
                            <strong>Groupe <?= htmlspecialchars((string) $teamNumber) ?></strong>
                            (<?= count($members) ?> membre<?= count($members) > 1 ? 's' : '' ?>)
                        </div>
                        <table>
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Statut</th>
                                    <th class="checkbox-col">Présent</th>
                                    <th class="checkbox-col">Absent</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($members as $user) : ?>
                                    <tr>
                                        <td><?= htmlspecialchars($user['last_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($user['first_name'] ?? '') ?></td>
                                        <td><?= htmlspecialchars($user['user_status'] ?? '') ?></td>
                                        <td class="checkbox-col"><div class="box"></div></td>
                                        <td class="checkbox-col"><div class="box"></div></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        <div class="team-separator"></div>
                    <?php endforeach; ?>
                <?php endif; ?>

                <?php if (empty($individualRegistrations) && empty($teamRegistrations)) : ?>
                    <p style="text-align: center; color: #666; margin-top: 50px;">
                        Aucune inscription pour cet événement.
                    </p>
                <?php endif; ?>
            </body>
        </html>

        <?php
        $html = ob_get_clean();

        // Generation
        $dompdf->loadHtml((string)$html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        // Send to navigator
        $dompdf->stream("inscrits_evenement_" . $event['event_name'] . ".pdf", ["Attachment" => true]);
        exit;
    }
}
