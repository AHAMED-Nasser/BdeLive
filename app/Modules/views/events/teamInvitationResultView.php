<?php
/**
 * Team Invitation Result View
 *
 * Displays the result of team invitation actions (success/failure).
 * Shows confirmation messages and next steps after invitation validation.
 *
 * @package BdeLive\Views\Events
 * @version 1.0.0
 * @author BdeLive - Group 8
 *
 * @var array<string, mixed> $invitation Invitation data with event info
 * @var bool $alreadyProcessed Whether the invitation was already processed
 * @var string $status The validation status (confirmed, declined)
 * @var bool|null $allConfirmed Whether all team members confirmed (only for confirmed status)
 * @var bool|null $teamCancelled Whether the team was cancelled (only for declined status)
 * @var \App\Core\Security\CsrfProtection $csrf
 * @var array<string, mixed>|null $user Current user data
 * @var array<string, string|null> $flash Flash messages
 */
start_page("Confirmation d'inscription - BDELive ", true, $user ?? null);

$isConfirmed = $status === 'confirmed';
$isDeclined = $status === 'declined';
?>

<div class="team-invitation-result-page">
    <?php if ($alreadyProcessed) : ?>
        <div class="team-invitation-result-icon team-invitation-result-icon--info">
            <i class="fas fa-info-circle" aria-hidden="true"></i>
        </div>
        <h2>Invitation déjà traitée</h2>
        <p>Statut : <strong class="team-invitation-result-status team-invitation-result-status--<?= $status ?>"><?= $status === 'confirmed' ? 'Confirmé' : 'Refusé' ?></strong></p>

    <?php elseif ($isConfirmed) : ?>
        <div class="team-invitation-result-icon team-invitation-result-icon--success">
            <i class="fas fa-check-circle" aria-hidden="true"></i>
        </div>
        <h2>Inscription au groupe confirmée</h2>
        <?php if (!empty($allConfirmed)) : ?>
            <p class="team-invitation-result-alert team-invitation-result-alert--success">Tous les membres ont confirmé. Le groupe est maintenant inscrit.</p>
        <?php else : ?>
            <p class="team-invitation-result-alert team-invitation-result-alert--warning">En attente de la confirmation des autres membres.</p>
        <?php endif; ?>

    <?php else : ?>
        <div class="team-invitation-result-icon team-invitation-result-icon--error">
            <i class="fas fa-times-circle" aria-hidden="true"></i>
        </div>
        <h2>Invitation refusée</h2>
        <?php if (!empty($teamCancelled)) : ?>
            <p class="team-invitation-result-alert team-invitation-result-alert--error">Le groupe a été annulé.</p>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php end_page(); ?>
