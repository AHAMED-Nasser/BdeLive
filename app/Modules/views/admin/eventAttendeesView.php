<?php
/**
 * @var array $event
 * @var array $attendees
 */
start_page("Inscrits - " . htmlspecialchars($event['title']), true, $user ?? null);
?>

    <main style="padding: 20px; max-width: 1000px; margin: 0 auto;">
        <h1>Liste des inscrits : <?= htmlspecialchars($event['title']) ?></h1>
        <p>Nombre total d'inscrits : <strong><?= count($attendees) ?></strong></p>

        <table style="width: 100%; border-collapse: collapse; margin-top: 20px; background: white;">
            <thead>
            <tr style="background: #0056b3; color: white;">
                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Nom</th>
                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Prénom</th>
                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Email</th>
                <th style="padding: 12px; text-align: left; border: 1px solid #ddd;">Date d'inscription</th>
            </tr>
            </thead>
            <tbody>
            <?php if (!empty($attendees)) : ?>
                <?php foreach ($attendees as $person) : ?>
                    <tr>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($person['lastname']) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($person['firstname']) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($person['email']) ?></td>
                        <td style="padding: 10px; border: 1px solid #ddd;"><?= htmlspecialchars($person['registration_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else : ?>
                <tr>
                    <td colspan="4" style="padding: 20px; text-align: center;">Aucun inscrit pour le moment.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>

        <div style="margin-top: 20px;">
            <a href="index.php?page=home" style="text-decoration: none; color: #0056b3;">← Retour à l'accueil</a>
        </div>
    </main>

<?php end_page(); ?>