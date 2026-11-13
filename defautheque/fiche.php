<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/fiche_defaut.php';

$user = utilisateur_connecte();
$id = (int)($_GET['id'] ?? 0);
$stmt = db()->prepare('SELECT * FROM defauts WHERE id = ? AND origine = "plastoyo"');
$stmt->execute([$id]);
$defaut = $stmt->fetch();

$titrePage = 'Fiche de défaut';
$pageActive = 'defautheque';
require __DIR__ . '/../includes/header.php';

if ($user && !acces_defautheque_autorise($user)):
    echo '<div class="bandeau-payant"><strong>Accès payant requis.</strong> Essai de ' . DUREE_ESSAI_MOIS . ' mois terminé — abonnement ' . ABONNEMENT_PRIX . ' à valider par l\'administrateur.</div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
endif;

if (!$defaut) {
    echo '<p>Défaut introuvable.</p>';
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

afficher_fiche_defaut($defaut, $causes, $commentaires);
echo '<p><a class="bouton bouton-secondaire" href="' . BASE_URL . '/defautheque/index.php">Retour à la défauthèque</a></p>';
if ($user) {
    echo '<p><a class="bouton" href="' . BASE_URL . '/defauts/commentaire.php?defaut_id=' . (int)$defaut['id'] . '">Ajouter un commentaire / une réponse</a></p>';
}
require __DIR__ . '/../includes/footer.php';
