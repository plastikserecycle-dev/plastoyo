<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/fiche_defaut.php';
$admin = exiger_admin();
$id = (int)($_GET['id'] ?? 0);

// L'admin peut commenter n'importe quel défaut (réponse nominative signée admin)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte = trim($_POST['texte'] ?? '');
    $parent = (int)($_POST['parent_id'] ?? 0) ?: null;
    if ($texte !== '') {
        db()->prepare('INSERT INTO commentaires_defaut (defaut_id, auteur, est_admin, texte, parent_id) VALUES (?, ?, 1, ?, ?)')
            ->execute([$id, $admin['nom'], $texte, $parent]);
    }
    header('Location: ' . BASE_URL . '/admin/defaut_view.php?id=' . $id);
    exit;
}

$stmt = db()->prepare('SELECT * FROM defauts WHERE id = ?');
$stmt->execute([$id]);
$defaut = $stmt->fetch();
$titrePage = 'Admin — Fiche défaut';
$pageActive = 'admin';
require __DIR__ . '/../includes/header.php';
if (!$defaut) {
    echo '<p>Défaut introuvable.</p>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}
$stmt = db()->prepare('SELECT c.categorie, c.valeur FROM defaut_cause dc JOIN cause c ON c.id = dc.cause_id WHERE dc.defaut_id = ? ORDER BY c.categorie, c.valeur');
$stmt->execute([$id]);
$causes = $stmt->fetchAll();
$stmt = db()->prepare('SELECT * FROM commentaires_defaut WHERE defaut_id = ? ORDER BY date_creation, id');
$stmt->execute([$id]);
$commentaires = $stmt->fetchAll();

afficher_fiche_defaut($defaut, $causes, $commentaires);

echo '<form class="formulaire" method="post" style="margin-top:18px">';
echo '<label for="texte">Ajouter un commentaire / une réponse (signé ' . h($admin['nom']) . ', administrateur)</label>';
echo '<select name="parent_id"><option value="">— Commentaire principal —</option>';
foreach ($commentaires as $c) {
    echo '<option value="' . (int)$c['id'] . '">' . h(mb_strimwidth($c['auteur'] . ' : ' . $c['texte'], 0, 70, '…')) . '</option>';
}
echo '</select><textarea id="texte" name="texte" required></textarea>';
echo '<button class="bouton" type="submit">Publier</button></form>';
echo '<p><a class="bouton bouton-secondaire" href="' . BASE_URL . '/admin/defauts.php">Retour à la liste</a></p>';
require __DIR__ . '/../includes/footer.php';
