<?php
/**
 * Team Invitation View
 *
 * Displays the team invitation interface for event participants.
 * Allows users to view team details, member status, and accept/decline invitations.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var array<string, mixed> $invitation Invitation data with event info
 * @var array<string, mixed>|null $team Team data
 * @var array<int, array<string, mixed>> $members Team members list
 * @var string $token Validation token
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages
 */
start_page("Invitation à rejoindre un groupe - BDELive", true, $user ?? null);
?>

<div class="team-invitation-page">
    <div class="team-invitation-card">
        <div class="team-invitation-header">
            <i class="fas fa-users" aria-hidden="true"></i>
            <h1>Invitation à un groupe</h1>
            <p>Vous avez été invité(e) à rejoindre un groupe</p>
        </div>

        <div class="team-invitation-event">
            <h2><?= htmlspecialchars($invitation['event_name'] ?? 'Événement') ?></h2>
            <p>📅 <strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($invitation['event_date']))) ?>
                à <?= htmlspecialchars(date('H:i', strtotime($invitation['event_time']))) ?></p>
        </div>

        <div class="team-invitation-team">
            <h3><i class="fas fa-flag" aria-hidden="true"></i> Groupe <?= $team ? htmlspecialchars($team['team_number']) : '?' ?></h3>
            <p>Membres de l'équipe :</p>
            <ul class="team-invitation-members">
                <?php foreach ($members as $member) : ?>
                    <?php
                    $isCurrentUser = strtolower($member['email']) === strtolower($invitation['email']);
                    $statusIcon = $member['validation_status'] === 'confirmed' ? '✅' : ($member['validation_status'] === 'declined' ? '❌' : '⏳');
                    $statusText = $member['validation_status'] === 'confirmed' ? 'Confirmé' : ($member['validation_status'] === 'declined' ? 'Refusé' : 'En attente');
                    ?>
                    <li class="team-invitation-member <?= $isCurrentUser ? 'team-invitation-member--current' : '' ?>">
                        <span>
                            <?php if ($member['first_name'] && $member['last_name']) : ?>
                                <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                            <?php else : ?>
                                <?= htmlspecialchars($member['email']) ?>
                            <?php endif; ?>
                            <?php if ($isCurrentUser) : ?>
                                <span class="team-invitation-member-you">(vous)</span>
                            <?php endif; ?>
                        </span>
                        <span class="team-invitation-member-status"><?= $statusIcon ?> <?= $statusText ?></span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <div class="team-invitation-actions">
            <p>Souhaitez-vous rejoindre ce groupe ?</p>
            <div class="team-invitation-buttons">
                <a href="index.php?page=validateTeamInvitation&token=<?= urlencode($token) ?>&action=confirm"
                    class="team-invitation-btn team-invitation-btn--accept"
                    data-confirm="Confirmez-vous votre participation à ce groupe ?">
                    <i class="fas fa-check" aria-hidden="true"></i> Accepter
                </a>
                <a href="index.php?page=validateTeamInvitation&token=<?= urlencode($token) ?>&action=decline"
                    class="team-invitation-btn team-invitation-btn--decline"
                    data-confirm="Êtes-vous sûr de vouloir refuser ? Le groupe entier sera annulé.">
                    <i class="fas fa-times" aria-hidden="true"></i> Refuser
                </a>
            </div>
            <div class="team-invitation-warning">
                <p><i class="fas fa-exclamation-triangle" aria-hidden="true"></i>
                    <strong>Attention :</strong> Si vous refusez, le groupe entier sera annulé et tous les autres
                    membres devront reformer un nouveau groupe.</p>
            </div>
        </div>
    </div>
</div>

<?php end_page(); ?>
