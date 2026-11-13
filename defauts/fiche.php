<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/fiche_defaut.php';
$user = exiger_utilisateur();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM defauts WHERE id = ? AND utilisateur_id = ?');
$stmt->execute([$id, $user['id']]);
$defaut = $stmt->fetch();

$titrePage = 'Ma fiche de défaut';
$pageActive = 'espace';
require __DIR__ . '/../includes/header.php';

if (!$defaut) {
    echo '<p>Défaut introuvable dans votre défauthèque.</p>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}
$stmt = db()->prepare('SELECT c.categorie, c.valeur FROM defaut_cause dc
    JOIN cause c ON c.id = dc.cause_id WHERE dc.defaut_id = ? ORDER BY c.categorie, c.valeur');
$stmt->execute([$id]);
$causes = $stmt->fetchAll();
$stmt = db()->prepare('SELECT * FROM commentaires_defaut WHERE defaut_id = ? ORDER BY date_creation, id');
$stmt->execute([$id]);
$commentaires = $stmt->fetchAll();

if (isset($_GET['enregistre'])) {
    echo '<div class="succes">Défaut enregistré. Il n\'est plus modifiable : vous pouvez y ajouter des commentaires et des réponses.</div>';
}
afficher_fiche_defaut($defaut, $causes, $commentaires);
echo '<p><a class="bouton bouton-secondaire" href="' . BASE_URL . '/compte/espace.php">Retour à mon espace</a> ';
echo '<a class="bouton" href="' . BASE_URL . '/defauts/commentaire.php?defaut_id=' . (int)$defaut['id'] . '">Ajouter un commentaire / une réponse</a></p>';
require __DIR__ . '/../includes/footer.php';
