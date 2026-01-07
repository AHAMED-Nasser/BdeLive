<?php
/**
 * Team Invitation Result View
 *
 * Displays the result of invitation validation (confirmed, declined, or already processed).
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
start_page("Résultat de l'invitation", true, $user ?? null);

$isConfirmed = $status === 'confirmed';
$isDeclined = $status === 'declined';
?>

<div class="container" style="max-width: 600px; margin: 40px auto; padding: 20px;">
    
    <div class="result-card" style="background: white; border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); overflow: hidden; text-align: center;">
        
        <!-- Header -->
        <?php if ($alreadyProcessed) : ?>
            <div style="background: linear-gradient(135deg, #6c757d 0%, #495057 100%); padding: 40px; color: white;">
                <i class="fas fa-info-circle" style="font-size: 64px; margin-bottom: 20px;"></i>
                <h1 style="margin: 0; font-size: 24px;">Invitation déjà traitée</h1>
            </div>
        <?php elseif ($isConfirmed) : ?>
            <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 40px; color: white;">
                <i class="fas fa-check-circle" style="font-size: 64px; margin-bottom: 20px;"></i>
                <h1 style="margin: 0; font-size: 24px;">Participation confirmée !</h1>
            </div>
        <?php else : ?>
            <div style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 40px; color: white;">
                <i class="fas fa-times-circle" style="font-size: 64px; margin-bottom: 20px;"></i>
                <h1 style="margin: 0; font-size: 24px;">Invitation refusée</h1>
            </div>
        <?php endif; ?>

        <!-- Content -->
        <div style="padding: 30px;">
            
            <?php if ($alreadyProcessed) : ?>
                <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                    Cette invitation a déjà été traitée.
                </p>
                <p style="color: #666;">
                    Statut actuel : 
                    <strong style="color: <?= $status === 'confirmed' ? '#28a745' : '#dc3545' ?>;">
                        <?= $status === 'confirmed' ? 'Confirmé' : 'Refusé' ?>
                    </strong>
                </p>
            
            <?php elseif ($isConfirmed) : ?>
                <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                    Votre participation au groupe a bien été enregistrée !
                </p>
                
                <?php if (!empty($allConfirmed)) : ?>
                    <div style="background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <i class="fas fa-trophy" style="font-size: 32px; color: #28a745; margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: #155724; font-weight: bold;">
                            🎉 Tous les membres ont confirmé !
                        </p>
                        <p style="margin: 10px 0 0 0; color: #155724;">
                            Votre groupe est maintenant officiellement inscrit à l'événement.
                        </p>
                    </div>
                <?php else : ?>
                    <div style="background: #fff3cd; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <i class="fas fa-hourglass-half" style="font-size: 32px; color: #856404; margin-bottom: 10px;"></i>
                        <p style="margin: 0; color: #856404;">
                            En attente des autres membres...
                        </p>
                        <p style="margin: 10px 0 0 0; color: #856404; font-size: 14px;">
                            Le groupe sera validé quand tous les membres auront confirmé leur participation.
                        </p>
                    </div>
                <?php endif; ?>
            
            <?php else : ?>
                <p style="font-size: 18px; color: #333; margin-bottom: 20px;">
                    Vous avez refusé l'invitation.
                </p>
                
                <?php if (!empty($teamCancelled)) : ?>
                    <div style="background: #f8d7da; padding: 20px; border-radius: 8px; margin: 20px 0;">
                        <p style="margin: 0; color: #721c24;">
                            Le groupe a été annulé. Les autres membres ont été notifiés et devront reformer un nouveau groupe s'ils souhaitent participer.
                        </p>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Event reminder -->
            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin-top: 20px;">
                <h3 style="margin: 0 0 10px 0; color: #333;">
                    <?= htmlspecialchars($invitation['event_name'] ?? 'Événement') ?>
                </h3>
                <p style="margin: 0; color: #666;">
                    📅 <?= htmlspecialchars(date('d/m/Y', strtotime($invitation['event_date']))) ?>
                    à <?= htmlspecialchars(date('H:i', strtotime($invitation['event_time']))) ?>
                </p>
                <p style="margin: 10px 0 0 0; color: #666;">
                    🏆 Groupe <?= htmlspecialchars($invitation['team_number'] ?? '?') ?>
                </p>
            </div>

            <!-- Actions -->
            <div style="margin-top: 30px;">
                <a href="index.php?page=showEvent&id=<?= $invitation['event_id'] ?>" 
                   style="display: inline-block; padding: 12px 30px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; text-decoration: none; border-radius: 8px; font-weight: bold;">
                    <i class="fas fa-arrow-right"></i> Voir l'événement
                </a>
            </div>

        </div>

    </div>

</div>

<?php end_page(); ?>

