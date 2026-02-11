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
start_page("BDELive - Site officiel", true, $user ?? null);

$isConfirmed = $status === 'confirmed';
$isDeclined = $status === 'declined';
?>

<div class="container" style="max-width: 500px; margin: 60px auto; padding: 20px; text-align: center;">

    <?php if ($alreadyProcessed) : ?>
        <div style="color: #6c757d; margin-bottom: 20px;">
            <i class="fas fa-info-circle" style="font-size: 48px;"></i>
        </div>
        <h2 style="color: #333; margin-bottom: 15px;">Invitation deja traitee</h2>
        <p style="color: #666;">
            Statut :
            <strong style="color: <?= $status === 'confirmed' ? '#28a745' : '#dc3545' ?>;">
                <?= $status === 'confirmed' ? 'Confirme' : 'Refuse' ?>
            </strong>
        </p>

    <?php elseif ($isConfirmed) : ?>
        <div style="color: #28a745; margin-bottom: 20px;">
            <i class="fas fa-check-circle" style="font-size: 48px;"></i>
        </div>
        <h2 style="color: #333; margin-bottom: 15px;">Inscription au groupe confirmee</h2>

        <?php if (!empty($allConfirmed)) : ?>
            <p style="color: #155724; background: #d4edda; padding: 15px; border-radius: 8px;">
                Tous les membres ont confirme. Le groupe est maintenant inscrit.
            </p>
        <?php else : ?>
            <p style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px;">
                En attente de la confirmation des autres membres.
            </p>
        <?php endif; ?>

    <?php else : ?>
        <div style="color: #dc3545; margin-bottom: 20px;">
            <i class="fas fa-times-circle" style="font-size: 48px;"></i>
        </div>
        <h2 style="color: #333; margin-bottom: 15px;">Invitation refusee</h2>

        <?php if (!empty($teamCancelled)) : ?>
            <p style="color: #721c24; background: #f8d7da; padding: 15px; border-radius: 8px;">
                Le groupe a ete annule.
            </p>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php end_page(); ?>
