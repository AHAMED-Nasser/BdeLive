<?php
/**
 * Group Registration View
 *
 * Displays the form for team/group registration for events.
 * Handles team member email input, validation, and invitation sending.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive - Group 8
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

<div class="group-registration-container">
    <h1>
        <i class="fas fa-users"></i> Inscription en groupe
    </h1>

    <div class="event-info">
        <h3><?= htmlspecialchars($event['event_name']) ?></h3>
        <p>
            <i class="fas fa-calendar-alt"></i> <?= htmlspecialchars(date('d/m/Y', strtotime($event['event_date']))) ?>
            à <?= htmlspecialchars(date('H:i', strtotime($event['event_time']))) ?>
        </p>
        <p>
            <i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($event['event_location']) ?>
        </p>
    </div>

    <!-- Flash messages -->
    <?php if (!empty($flash['success'])) : ?>
        <div class="group-flash-success">
            <?= htmlspecialchars($flash['success']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['error'])) : ?>
        <div class="group-flash-error">
            <?= htmlspecialchars($flash['error']) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($flash['warning'])) : ?>
        <div class="group-flash-warning">
            <?= htmlspecialchars($flash['warning']) ?>
        </div>
    <?php endif; ?>

    <form action="index.php?page=groupRegistration&event_id=<?= $event['event_id'] ?>" method="POST" class="group-form">
        <?= $csrf->getTokenField() ?>
        <input type="hidden" name="action" value="submitGroup">

        <div class="team-size-info">
            <span>
                <i class="fas fa-user-friends"></i>
                Groupe de <strong><?= $teamSize ?></strong> personnes
                (vous + <?= $requiredMembers ?> membre<?= $requiredMembers > 1 ? 's' : '' ?>)
            </span>
        </div>

        <div class="warning-box">
            <p>
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Important :</strong> Assurez-vous que les adresses email sont correctes.
                Les membres invités recevront un email pour confirmer leur participation.
            </p>
        </div>

        <h4>
            <i class="fas fa-envelope"></i> Invitez vos coéquipiers
        </h4>

        <div id="members-container">
            <?php for ($i = 1; $i <= $requiredMembers; $i++) : ?>
                <div class="member-input">
                    <label for="member-<?= $i ?>">
                        Membre <?= $i ?>
                    </label>
                    <input type="email" id="member-<?= $i ?>" name="member_emails[]" placeholder="email@exemple.com"
                        required>
                </div>
            <?php endfor; ?>
        </div>

        <div class="form-actions">
            <button type="submit">
                <i class="fas fa-paper-plane"></i> Créer le groupe et envoyer les invitations
            </button>

            <a href="index.php?page=showEvent&id=<?= $event['event_id'] ?>">
                <i class="fas fa-arrow-left"></i> Retour à l'événement
            </a>
        </div>
    </form>

    <?php if (!empty($userTeams)) : ?>
        <div class="existing-teams">
            <h4>
                <i class="fas fa-history"></i> Vos groupes en cours
            </h4>
            <?php foreach ($userTeams as $team) : ?>
                <div
                    class="team-card <?= $team['status'] === 'confirmed' ? 'confirmed' : ($team['status'] === 'pending' ? 'pending' : 'cancelled') ?>">
                    <strong>Groupe <?= $team['team_number'] ?></strong>
                    <span
                        class="status-badge <?= $team['status'] === 'confirmed' ? 'confirmed' : ($team['status'] === 'pending' ? 'pending' : 'cancelled') ?>">
                        <?= $team['status'] === 'confirmed' ? 'Confirmé' : ($team['status'] === 'pending' ? 'En attente' : 'Annulé') ?>
                    </span>
                    <p>
                        Créé le <?= date('d/m/Y à H:i', strtotime($team['creation_date'])) ?>
                    </p>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php end_page(); ?>
