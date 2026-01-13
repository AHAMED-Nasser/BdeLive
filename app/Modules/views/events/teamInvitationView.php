<?php
/**
 * Team Invitation View
 *
 * Displays the team invitation interface for event participants.
 * Allows users to view team details, member status, and accept/decline invitations.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive Team
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

<div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px;">

    <div class="invitation-card"
        style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden;">

        <!-- Header -->
        <div
            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; color: white;">
            <i class="fas fa-users" style="font-size: 48px; margin-bottom: 15px;"></i>
            <h1 style="margin: 0; font-size: 24px;">Invitation à un groupe</h1>
            <p style="margin: 10px 0 0 0; opacity: 0.9;">Vous avez été invité(e) à rejoindre un groupe</p>
        </div>

        <!-- Event info -->
        <div style="padding: 25px; border-bottom: 1px solid #eee;">
            <h2 style="margin: 0 0 15px 0; color: #333;">
                <?= htmlspecialchars($invitation['event_name'] ?? 'Événement') ?>
            </h2>
            <p style="margin: 0; color: #666;">
                📅 <strong>Date :</strong> <?= htmlspecialchars(date('d/m/Y', strtotime($invitation['event_date']))) ?>
                à <?= htmlspecialchars(date('H:i', strtotime($invitation['event_time']))) ?>
            </p>
        </div>

        <!-- Team info -->
        <div style="padding: 25px; background: #f8f9fa;">
            <h3 style="margin: 0 0 15px 0; color: #333;">
                <i class="fas fa-flag"></i> Groupe <?= $team ? htmlspecialchars($team['team_number']) : '?' ?>
            </h3>

            <p style="margin: 0 0 15px 0; color: #666;">
                Membres de l'équipe :
            </p>

            <ul style="list-style: none; padding: 0; margin: 0;">
                <?php foreach ($members as $member): ?>
                    <?php
                    $isCurrentUser = strtolower($member['email']) === strtolower($invitation['email']);
                    $statusIcon = $member['validation_status'] === 'confirmed' ? '✅' : ($member['validation_status'] === 'declined' ? '❌' : '⏳');
                    $statusText = $member['validation_status'] === 'confirmed' ? 'Confirmé' : ($member['validation_status'] === 'declined' ? 'Refusé' : 'En attente');
                    ?>
                    <li
                        style="padding: 10px; margin-bottom: 8px; background: white; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; <?= $isCurrentUser ? 'border: 2px solid #667eea;' : '' ?>">
                        <span>
                            <?php if ($member['first_name'] && $member['last_name']): ?>
                                <?= htmlspecialchars($member['first_name'] . ' ' . $member['last_name']) ?>
                            <?php else: ?>
                                <?= htmlspecialchars($member['email']) ?>
                            <?php endif; ?>
                            <?php if ($isCurrentUser): ?>
                                <span style="color: #667eea; font-size: 12px;">(vous)</span>
                            <?php endif; ?>
                        </span>
                        <span style="font-size: 12px; color: #666;">
                            <?= $statusIcon ?>     <?= $statusText ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <!-- Actions -->
        <div style="padding: 25px; text-align: center;">
            <p style="margin: 0 0 20px 0; color: #666;">
                Souhaitez-vous rejoindre ce groupe ?
            </p>

            <div style="display: flex; gap: 15px; justify-content: center; flex-wrap: wrap;">
                <a href="index.php?page=validateTeamInvitation&token=<?= urlencode($token) ?>&action=confirm"
                    style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: transform 0.2s;"
                    onclick="return confirm('Confirmez-vous votre participation à ce groupe ?');">
                    <i class="fas fa-check"></i> Accepter
                </a>

                <a href="index.php?page=validateTeamInvitation&token=<?= urlencode($token) ?>&action=decline"
                    style="display: inline-block; padding: 15px 30px; background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold; transition: transform 0.2s;"
                    onclick="return confirm('Êtes-vous sûr de vouloir refuser ? Le groupe entier sera annulé.');">
                    <i class="fas fa-times"></i> Refuser
                </a>
            </div>

            <div
                style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 8px; border-left: 4px solid #ffc107;">
                <p style="margin: 0; color: #856404; font-size: 13px;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Attention :</strong> Si vous refusez, le groupe entier sera annulé et tous les autres
                    membres devront reformer un nouveau groupe.
                </p>
            </div>
        </div>

    </div>

</div>

<style>
    a[href*="confirm"]:hover,
    a[href*="decline"]:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
</style>

<?php end_page(); ?>