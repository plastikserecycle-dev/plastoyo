<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/champs_defaut.php';
exiger_admin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$champs = champs_defaut();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [];
    foreach (array_keys($champs) as $c) {
        $donnees[$c] = trim($_POST[$c] ?? '');
    }
    $photos = [];
    foreach ([1, 2, 3] as $n) {
        $up = upload_fichier($_FILES['photo' . $n] ?? ['name' => '', 'error' => UPLOAD_ERR_NO_FILE], 'images', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
        if ($up !== null) {
            $photos[$n] = $up;
        } elseif ($id > 0) {
            $stmt = db()->prepare('SELECT photo' . $n . ' FROM defauts WHERE id = ?');
            $stmt->execute([$id]);
            $photos[$n] = $stmt->fetchColumn() ?: null;
        } else {
            $photos[$n] = null;
        }
    }
    $causeIds = array_map('intval', $_POST['causes'] ?? []);
    $date = trim($_POST['date_enregistrement'] ?? '') ?: date('Y-m-d');

    $colonnes = array_merge(['origine', 'date_enregistrement'], array_keys($champs), ['photo1', 'photo2', 'photo3']);
    $valeurs = array_merge(['plastoyo', $date], array_values($donnees), array_values($photos));
    db()->beginTransaction();
    if ($id > 0) {
        $set = implode(',', array_map(fn($c) => "$c = ?", $colonnes));
        db()->prepare("UPDATE defauts SET $set WHERE id = ? AND origine = 'plastoyo'")
            ->execute(array_merge($valeurs, [$id]));
        db()->prepare('DELETE FROM defaut_cause WHERE defaut_id = ?')->execute([$id]);
    } else {
        db()->prepare('INSERT INTO defauts (' . implode(',', $colonnes) . ') VALUES (' . implode(',', array_fill(0, count($colonnes), '?')) . ')')
            ->execute($valeurs);
        $id = (int)db()->lastInsertId();
    }
    $stmtC = db()->prepare('INSERT IGNORE INTO defaut_cause (defaut_id, cause_id) VALUES (?, ?)');
    foreach ($causeIds as $cid) {
        if ($cid > 0) $stmtC->execute([$id, $cid]);
    }
    db()->commit();
    header('Location: ' . BASE_URL . '/admin/defaut_view.php?id=' . $id);
    exit;
}

$defaut = ['date_enregistrement' => date('Y-m-d')];
if ($id > 0) {
    $stmt = db()->prepare("SELECT * FROM defauts WHERE id = ? AND origine = 'plastoyo'");
    $stmt->execute([$id]);
    $defaut = $stmt->fetch() ?: $defaut;
}
$causeCochees = [];
if ($id > 0) {
    $stmt = db()->prepare('SELECT cause_id FROM defaut_cause WHERE defaut_id = ?');
    $stmt->execute([$id]);
    $causeCochees = array_column($stmt->fetchAll(), 'cause_id');
}

$causes = db()->query('SELECT * FROM cause ORDER BY categorie, valeur')->fetchAll();
$parCategorie = [];
foreach ($causes as $c) { $parCategorie[$c['categorie']][] = $c; }
$noms = ['matiere' => 'Causes matière', 'moule' => 'Causes moule', 'machine' => 'Causes machine',
         'materiel' => 'Causes matériel', 'montage' => 'Causes montage (alarmes)', 'mode' => 'Mode'];

$titrePage = 'Admin — Défaut plastoyo';
$pageActive = 'admin';
require __DIR__ . '/../includes/header.php';
echo '<h1 class="page">' . ($id > 0 ? 'Modifier le défaut plastoyo n°' . $id : 'Nouveau défaut dans la défauthèque plastoyo') . '</h1>';
echo '<p><a href="' . BASE_URL . '/admin/defauts.php">← Retour à la liste</a>. Chaque valeur n\'est pas obligatoire et reste modifiable à tout moment.</p>';
?>
<form class="formulaire" method="post" enctype="multipart/form-data">
  <input type="hidden" name="id" value="<?= $id ?>">
  <label for="date_enregistrement">Date</label>
  <input type="date" id="date_enregistrement" name="date_enregistrement" value="<?= h($defaut['date_enregistrement']) ?>">
  <fieldset>
    <legend>Photos du défaut (3 maximum)</legend>
    <?php foreach ([1, 2, 3] as $n): $p = 'photo' . $n; ?>
      <label>Photo <?= $n ?></label>
      <?php if (!empty($defaut[$p])): ?><p class="fichier-actuel">Actuelle : <a href="<?= h(url_fichier($defaut[$p])) ?>" target="_blank"><?= h(basename($defaut[$p])) ?></a></p><?php endif; ?>
      <input type="file" name="<?= $p ?>" accept="image/*">
    <?php endforeach; ?>
  </fieldset>
  <div class="champ-ligne">
  <?php foreach ($champs as $cle => [$label, $type]): ?>
    <div>
      <label for="<?= h($cle) ?>"><?= h($label) ?></label>
      <?php if ($type === 'textarea'): ?>
        <textarea id="<?= h($cle) ?>" name="<?= h($cle) ?>"><?= h($defaut[$cle] ?? '') ?></textarea>
      <?php else: ?>
        <input type="text" id="<?= h($cle) ?>" name="<?= h($cle) ?>" value="<?= h($defaut[$cle] ?? '') ?>">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
  <fieldset>
    <legend>Causes cochées (cliquez sur « ? » pour l'explication)</legend>
    <?php foreach ($parCategorie as $cat => $liste): ?>
      <div class="zone-causes">
        <h3><?= h($noms[$cat] ?? ucfirst($cat)) ?></h3>
        <?php foreach ($liste as $c): ?>
          <label>
            <input type="checkbox" name="causes[]" value="<?= (int)$c['id'] ?>" id="cause-<?= (int)$c['id'] ?>"<?= in_array((int)$c['id'], $causeCochees, true) ? ' checked' : '' ?>>
            <?= h($c['valeur']) ?>
            <button type="button" class="btn-explication"
              data-cible="cause-<?= (int)$c['id'] ?>"
              data-titre="<?= h($c['valeur']) ?>"
              data-explication="<?= h($c['explication']) ?>"
              data-video="<?= h(url_fichier($c['video'])) ?>">?</button>
          </label>
        <?php endforeach; ?>
      </div>
    <?php endforeach; ?>
  </fieldset>
  <button class="bouton" type="submit">Enregistrer dans la défauthèque plastoyo</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
