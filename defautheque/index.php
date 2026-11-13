<?php
require __DIR__ . '/../config/config.php';
$titrePage = 'Défauthèque Plastoyo';
$pageActive = 'defautheque';
require __DIR__ . '/../includes/header.php';

$user = utilisateur_connecte();
if ($user && !acces_defautheque_autorise($user)):
?>
  <h1 class="page">Défauthèque générale Plastoyo</h1>
  <div class="bandeau-payant">
    <strong>Accès payant requis.</strong> Votre essai gratuit de <?= DUREE_ESSAI_MOIS ?> mois est terminé.
    L'accès à la défauthèque générale plastoyo devient payant : <strong><?= ABONNEMENT_PRIX ?></strong>.
    Faites valider votre paiement de 100&nbsp;€ par l'administrateur du site pour réactiver l'accès.
  </div>
<?php
  require __DIR__ . '/../includes/footer.php';
  exit;
endif;

$recherche = trim($_GET['q'] ?? '');
if ($recherche !== '') {
    $stmt = db()->prepare("SELECT * FROM defauts WHERE origine = 'plastoyo'
        AND (defaut_constate LIKE ? OR ref_matiere LIKE ? OR famille_abbr LIKE ?) ORDER BY date_enregistrement DESC, id DESC");
    $like = '%' . $recherche . '%';
    $stmt->execute([$like, $like, $like]);
} else {
    $stmt = db()->query("SELECT * FROM defauts WHERE origine = 'plastoyo' ORDER BY date_enregistrement DESC, id DESC");
}
$defauts = $stmt->fetchAll();
?>
<h1 class="page">Défauthèque générale Plastoyo</h1>
<p>Photos des défauts enregistrés par l'administrateur plastoyo, avec l'explicatif associé.
   Cliquez sur une fiche pour voir le détail complet. Accès gratuit <?= DUREE_ESSAI_MOIS ?> mois après création de compte, puis <?= ABONNEMENT_PRIX ?>.</p>
<form class="recherche" method="get">
  <input type="text" name="q" placeholder="Rechercher un défaut, une matière, une famille..." value="<?= h($recherche) ?>">
  <button class="bouton" type="submit">Rechercher</button>
</form>
<?php if (!$defauts): ?>
  <p>Aucun défaut enregistré pour le moment.</p>
<?php else: ?>
<div class="liste-defauts">
  <?php foreach ($defauts as $d): ?>
    <a class="carte-defaut" href="<?= BASE_URL ?>/defautheque/fiche.php?id=<?= (int)$d['id'] ?>">
      <?php if ($d['photo1']): ?>
        <img src="<?= h(url_fichier($d['photo1'])) ?>" alt="Photo du défaut">
      <?php else: ?>
        <img src="data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='160'><rect width='100%25' height='100%25' fill='%23dde4ea'/><text x='50%25' y='50%25' font-size='14' text-anchor='middle' fill='%23778'>pas de photo</text></svg>" alt="pas de photo">
      <?php endif; ?>
      <div class="corps">
        <h3><?= h(mb_strimwidth($d['defaut_constate'] ?? 'Défaut sans titre', 0, 80, '…')) ?></h3>
        <span class="date"><?= h($d['date_enregistrement']) ?><?= $d['famille_abbr'] ? ' — ' . h($d['famille_abbr']) : '' ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
