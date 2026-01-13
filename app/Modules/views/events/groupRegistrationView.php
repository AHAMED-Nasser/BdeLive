<?php
/**
 * Group Registration View
 *
 * Displays the form for team/group registration for events.
 * Handles team member email input, validation, and invitation sending.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive Team
 *
 * @var array<string, mixed> $event Event data
 * @var int $teamSize Maximum team size
 * @var array<int, array<string, mixed>> $userTeams User's existing teams
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages
 */
start_page("BDELive - Inscription en groupe : " . htmlspecialchars($event['event_name']), true, $user ?? null);

$requiredMembers = $teamSize - 1; // Creator is auto-included
?>

<div class="container" style="max-width: 700px; margin: 40px auto; padding: 20px;">
    <h1 class="text-center" style="margin-bottom: 30px;">
        <i class="fas fa-users"></i> Inscription en groupe
    </h1>

    <div class="event-info" style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
        <h3 style="margin: 0 0 10px 0;"><?= htmlspecialchars($event['event_name']) ?></h3>
        <p style="margin: 0; color: #666;">
            📅 <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?>
            à <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?>
        </p>
        <p style="margin: 10px 0 0 0; color: #666;">
            📍 <?= htmlspecialchars($event['event_location']) ?>
        </p>
    </div>

    <!-- Flash messages -->
    <?php foreach (['success' => '#d4edda', 'error' => '#f8d7da', 'warning' => '#fff3cd'] as $type => $color) : ?>
        <?php if (!empty($flash[$type])) : ?>
            <div
                style="background-color: <?= $color ?>; color: #<?= $type === 'success' ? '155724' : ($type === 'error' ? '721c24' : '856404') ?>; padding: 12px; margin: 20px 0; border-radius: 4px; text-align: center;">
                <?= htmlspecialchars($flash[$type]) ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

    <div class="group-info"
        style="background: #e3f2fd; padding: 20px; border-radius: 8px; margin-bottom: 30px; border-left: 4px solid #2196f3;">
        <h4 style="margin: 0 0 10px 0; color: #1565c0;">
            <i class="fas fa-info-circle"></i> Comment ça fonctionne ?
        </h4>
        <ol style="margin: 0; padding-left: 20px; color: #333;">
            <li>Renseignez les adresses email de vos coéquipiers (<?= $requiredMembers ?> personne(s) requise(s))</li>
            <li>Chaque membre recevra un email avec un lien de validation</li>
            <li>Le groupe sera officiellement inscrit quand <strong>tous les membres</strong> auront validé</li>
        </ol>
    </div>

    <div class="team-size-info"
        style="text-align: center; margin-bottom: 30px; padding: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 8px; color: white;">
        <span style="font-size: 18px;">
            <i class="fas fa-user-friends"></i>
            Groupe de <strong><?= $teamSize ?></strong> personnes
            (vous + <?= $requiredMembers ?> membre<?= $requiredMembers > 1 ? 's' : '' ?>)
        </span>
    </div>

    <form action="index.php?page=groupRegistration&event_id=<?= $event['event_id'] ?>" method="POST" class="group-form">
        <?= $csrf->getTokenField() ?>
        <input type="hidden" name="action" value="submitGroup">

        <div class="creator-info"
            style="background: #e8f5e9; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #4caf50;">
            <p style="margin: 0; color: #2e7d32;">
                <i class="fas fa-check-circle"></i>
                <strong>Vous (créateur du groupe)</strong><br>
                <span style="color: #666;"><?= htmlspecialchars($user['email'] ?? '') ?></span>
                <span style="color: #4caf50; font-size: 12px; margin-left: 10px;">✓ Automatiquement confirmé</span>
            </p>
        </div>

        <h4 style="margin-bottom: 15px;">
            <i class="fas fa-envelope"></i> Invitez vos coéquipiers
        </h4>

        <div id="members-container">
            <?php for ($i = 1; $i <= $requiredMembers; $i++) : ?>
                <div class="member-input" style="margin-bottom: 15px;">
                    <label for="member-<?= $i ?>" style="display: block; margin-bottom: 5px; font-weight: 500;">
                        Membre <?= $i ?>
                    </label>
                    <input type="email" id="member-<?= $i ?>" name="member_emails[]" placeholder="email@exemple.com"
                        required
                        style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 8px; font-size: 16px;">
                </div>
            <?php endfor; ?>
        </div>

        <div class="warning-box"
            style="background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404; font-size: 14px;">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important :</strong> Assurez-vous que les adresses email sont correctes.
                Les membres invités recevront un email pour confirmer leur participation.
            </p>
        </div>

        <div class="form-actions" style="margin-top: 30px;">
            <button type="submit"
                style="width: 100%; padding: 15px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; border: none; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: transform 0.2s;">
                <i class="fas fa-paper-plane"></i> Créer le groupe et envoyer les invitations
            </button>

            <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>"
                style="display: block; text-align: center; margin-top: 15px; padding: 12px; background: #6c757d; color: white; border-radius: 8px; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Retour à l'événement
            </a>
        </div>
    </form>

    <?php if (!empty($userTeams)) : ?>
        <div class="existing-teams" style="margin-top: 40px; padding-top: 30px; border-top: 2px solid #eee;">
            <h4 style="margin-bottom: 15px;">
                <i class="fas fa-history"></i> Vos groupes en cours
            </h4>
            <?php foreach ($userTeams as $team) : ?>
                <div class="team-card"
                    style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid <?= $team['status'] === 'confirmed' ? '#28a745' : ($team['status'] === 'pending' ? '#ffc107' : '#dc3545') ?>;">
                    <strong>Groupe <?= $team['team_number'] ?></strong>
                    <span
                        style="float: right; font-size: 12px; padding: 4px 8px; border-radius: 4px; background: <?= $team['status'] === 'confirmed' ? '#d4edda' : ($team['status'] === 'pending' ? '#fff3cd' : '#f8d7da') ?>; color: <?= $team['status'] === 'confirmed' ? '#155724' : ($team['status'] === 'pending' ? '#856404' : '#721c24') ?>;">
                        <?= $team['status'] === 'confirmed' ? 'Confirmé' : ($team['status'] === 'pending' ? 'En attente' : 'Annulé') ?>
                    </span>
                    <p style="margin: 10px 0 0 0; font-size: 13px; color: #666;">
                        Créé le <?= date('d/m/Y à H:i', strtotime($team['creation_date'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
    .member-input input:focus {
        border-color: #667eea;
        outline: none;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
    }

    button[type="submit"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
    }

    @media (max-width: 768px) {
        .container {
            padding: 15px;
            margin: 20px auto;
        }
    }
</style>

<?php end_page(); ?>
