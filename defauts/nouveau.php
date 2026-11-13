<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/champs_defaut.php';
$user = exiger_utilisateur();

if (!acces_defautheque_autorise($user)) {
    $titrePage = 'Enregistrer un défaut';
    $pageActive = 'espace';
    require __DIR__ . '/../includes/header.php';
    echo '<h1 class="page">Enregistrer un défaut</h1>';
    echo '<div class="bandeau-payant"><strong>Accès payant requis.</strong> L\'espace de création de la défauthèque est gratuit '
        . DUREE_ESSAI_MOIS . ' mois après la création du compte, puis payant (' . ABONNEMENT_PRIX . '). '
        . 'Faites valider votre paiement par l\'administrateur pour continuer.</div>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$champs = champs_defaut();

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $donnees = [];
    foreach (array_keys($champs) as $c) {
        $donnees[$c] = trim($_POST[$c] ?? '');
    }
    $photos = [];
    foreach ([1, 2, 3] as $n) {
        $photos[$n] = upload_fichier($_FILES['photo' . $n] ?? ['name' => '', 'error' => UPLOAD_ERR_NO_FILE], 'images', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }
    $causeIds = array_map('intval', $_POST['causes'] ?? []);

    $colonnes = array_merge(['origine', 'utilisateur_id', 'nom_personne', 'equipe', 'heure_enregistrement', 'date_enregistrement'],
        array_keys($champs), ['photo1', 'photo2', 'photo3']);
    $valeurs = ['operateur', $user['id'], $user['nom'], $user['equipe'], date('H:i:s'), date('Y-m-d')];
    foreach (array_keys($champs) as $c) { $valeurs[] = $donnees[$c]; }
    $valeurs[] = $photos[1]; $valeurs[] = $photos[2]; $valeurs[] = $photos[3];

    db()->beginTransaction();
    $sql = 'INSERT INTO defauts (' . implode(',', $colonnes) . ') VALUES (' . implode(',', array_fill(0, count($colonnes), '?')) . ')';
    $stmt = db()->prepare($sql);
    $stmt->execute($valeurs);
    $did = (int)db()->lastInsertId();
    $stmtC = db()->prepare('INSERT IGNORE INTO defaut_cause (defaut_id, cause_id) VALUES (?, ?)');
    foreach ($causeIds as $cid) {
        if ($cid > 0) $stmtC->execute([$did, $cid]);
    }
    db()->commit();
    header('Location: ' . BASE_URL . '/defauts/fiche.php?id=' . $did . '&enregistre=1');
    exit;
}

$causes = db()->query('SELECT * FROM cause ORDER BY categorie, valeur')->fetchAll();
$parCategorie = [];
foreach ($causes as $c) { $parCategorie[$c['categorie']][] = $c; }
$noms = ['matiere' => 'Causes matière', 'moule' => 'Causes moule', 'machine' => 'Causes machine',
         'materiel' => 'Causes matériel', 'montage' => 'Causes montage (alarmes)', 'mode' => 'Mode'];

$titrePage = 'Enregistrer un défaut';
$pageActive = 'espace';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page">Enregistrer un défaut dans ma défauthèque</h1>
<?php if ($erreur): ?><div class="erreur"><?= h($erreur) ?></div><?php endif; ?>
<p>Enregistrement par <strong><?= h($user['nom']) ?></strong> — équipe <?= h($user['equipe'] ?: 'non renseignée') ?>
   — le <?= date('d/m/Y') ?> à <?= date('H:i') ?>. Une fois enregistré, le défaut n'est plus modifiable :
   seules des commentaires/réponses pourront y être ajoutées.</p>
<form class="formulaire" method="post" enctype="multipart/form-data">
  <fieldset>
    <legend>Photos du défaut (3 maximum)</legend>
    <label>Photo 1 <input type="file" name="photo1" accept="image/*"></label>
    <label>Photo 2 <input type="file" name="photo2" accept="image/*"></label>
    <label>Photo 3 <input type="file" name="photo3" accept="image/*"></label>
  </fieldset>
  <div class="champ-ligne">
  <?php foreach ($champs as $cle => [$label, $type]): ?>
    <div>
      <label for="<?= h($cle) ?>"><?= h($label) ?></label>
      <?php if ($type === 'textarea'): ?>
        <textarea id="<?= h($cle) ?>" name="<?= h($cle) ?>"><?= h($_POST[$cle] ?? '') ?></textarea>
      <?php else: ?>
        <input type="text" id="<?= h($cle) ?>" name="<?= h($cle) ?>" value="<?= h($_POST[$cle] ?? '') ?>">
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
  </div>
  <fieldset>
    <legend>Causes (cliquez sur « ? » pour lire l'explication avant de cocher)</legend>
    <?php foreach ($parCategorie as $cat => $liste): ?>
      <div class="zone-causes">
        <h3><?= h($noms[$cat] ?? ucfirst($cat)) ?></h3>
        <?php foreach ($liste as $c): ?>
          <label>
            <input type="checkbox" name="causes[]" value="<?= (int)$c['id'] ?>" id="cause-<?= (int)$c['id'] ?>">
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
  <button class="bouton" type="submit">Enregistrer le défaut</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
