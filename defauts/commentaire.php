<?php
require __DIR__ . '/../config/config.php';
$user = exiger_utilisateur();
$defautId = (int)($_GET['defaut_id'] ?? $_POST['defaut_id'] ?? 0);

// Le défaut doit appartenir à l'opérateur, ou être un défaut de la défauthèque générale
$stmt = db()->prepare('SELECT * FROM defauts WHERE id = ? AND (utilisateur_id = ? OR origine = "plastoyo")');
$stmt->execute([$defautId, $user['id']]);
$defaut = $stmt->fetch();
if (!$defaut) {
    header('Location: ' . BASE_URL . '/compte/espace.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $texte = trim($_POST['texte'] ?? '');
    $parent = (int)($_POST['parent_id'] ?? 0) ?: null;
    if ($texte !== '') {
        $stmt = db()->prepare('INSERT INTO commentaires_defaut (defaut_id, auteur, est_admin, texte, parent_id) VALUES (?, ?, 0, ?, ?)');
        $stmt->execute([$defautId, $user['nom'], $texte, $parent]);
    }
    $retour = $defaut['origine'] === 'plastoyo'
        ? BASE_URL . '/defautheque/fiche.php?id=' . $defautId
        : BASE_URL . '/defauts/fiche.php?id=' . $defautId;
    header('Location: ' . $retour);
    exit;
}

$stmt = db()->prepare('SELECT * FROM commentaires_defaut WHERE defaut_id = ? ORDER BY date_creation, id');
$stmt->execute([$defautId]);
$commentaires = $stmt->fetchAll();

$titrePage = 'Commentaire';
$pageActive = 'espace';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page">Commentaire / réponse sur le défaut n°<?= $defautId ?></h1>
<p>Chaque commentaire est <strong>nominatif</strong> (signé <?= h($user['nom']) ?>) et peut recevoir des réponses.</p>
<form class="formulaire" method="post">
  <input type="hidden" name="defaut_id" value="<?= $defautId ?>">
  <label for="parent_id">Répondre à un commentaire existant (optionnel)</label>
  <select id="parent_id" name="parent_id">
    <option value="">— Commentaire principal —</option>
    <?php foreach ($commentaires as $c): ?>
      <option value="<?= (int)$c['id'] ?>"><?= h(mb_strimwidth($c['auteur'] . ' : ' . $c['texte'], 0, 70, '…')) ?></option>
    <?php endforeach; ?>
  </select>
  <label for="texte">Votre commentaire *</label>
  <textarea id="texte" name="texte" required></textarea>
  <button class="bouton" type="submit">Publier</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
