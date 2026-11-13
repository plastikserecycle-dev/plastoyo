<?php
require __DIR__ . '/../config/config.php';
exiger_admin();

// Validation du paiement de l'abonnement (100 EUR/an) ou expiration
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $uid = (int)$_POST['utilisateur_id'];
    $action = $_POST['action'] ?? '';
    if ($action === 'payer') {
        db()->prepare("UPDATE utilisateurs SET statut_abonnement = 'paye', date_paiement = NOW(), date_expiration = DATE_ADD(NOW(), INTERVAL 1 YEAR) WHERE id = ?")
            ->execute([$uid]);
    } elseif ($action === 'expire') {
        db()->prepare("UPDATE utilisateurs SET statut_abonnement = 'expire' WHERE id = ?")->execute([$uid]);
    } elseif ($action === 'essai') {
        db()->prepare("UPDATE utilisateurs SET statut_abonnement = 'essai', date_paiement = NULL, date_expiration = NULL WHERE id = ?")->execute([$uid]);
    }
    header('Location: ' . BASE_URL . '/admin/utilisateurs.php');
    exit;
}

$users = db()->query('SELECT * FROM utilisateurs ORDER BY date_inscription DESC')->fetchAll();
$titrePage = 'Admin — Comptes opérateurs';
$pageActive = 'admin';
require __DIR__ . '/../includes/header.php';
echo '<h1 class="page">Comptes opérateurs et abonnements</h1>';
echo '<p>Rappel : essai gratuit ' . DUREE_ESSAI_MOIS . ' mois après la création du compte, puis abonnement payant <strong>' . ABONNEMENT_PRIX . '</strong> (à valider ici après réception du paiement).</p>';
echo '<table class="donnees"><tr><th>ID</th><th>Nom</th><th>Email</th><th>Équipe</th><th>Fonctions</th><th>Inscription</th><th>Essai restant</th><th>Statut</th><th>Expiration</th><th>Actions</th></tr>';
foreach ($users as $u) {
    $stmt = db()->prepare('SELECT f.nom FROM utilisateur_fonctions uf JOIN fonctions f ON f.id = uf.fonction_id WHERE uf.utilisateur_id = ?');
    $stmt->execute([$u['id']]);
    $fonctions = implode(', ', array_column($stmt->fetchAll(), 'nom'));
    $essai = $u['statut_abonnement'] === 'essai' ? jours_restants_essai($u) . ' j' : '—';
    echo '<tr><td>' . (int)$u['id'] . '</td><td>' . h($u['nom']) . '</td><td>' . h($u['email']) . '</td><td>' . h($u['equipe']) . '</td><td>' . h($fonctions) . '</td>';
    echo '<td>' . h($u['date_inscription']) . '</td><td>' . $essai . '</td><td><strong>' . h($u['statut_abonnement']) . '</strong></td><td>' . h($u['date_expiration'] ?? '') . '</td><td>';
    echo '<form method="post" style="display:inline">'
        . '<input type="hidden" name="utilisateur_id" value="' . (int)$u['id'] . '">'
        . '<button class="bouton" name="action" value="payer" type="submit">Valider paiement 100 € (1 an)</button> '
        . '<button class="bouton bouton-secondaire" name="action" value="expire" type="submit">Expirer</button> '
        . '<button class="bouton bouton-secondaire" name="action" value="essai" type="submit">Remettre en essai</button></form>';
    echo '</td></tr>';
}
echo '</table>';
echo '<p><a href="' . BASE_URL . '/admin/index.php">← Tableau de bord</a></p>';
require __DIR__ . '/../includes/footer.php';
