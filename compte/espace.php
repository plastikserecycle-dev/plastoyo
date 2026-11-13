<?php
require __DIR__ . '/../config/config.php';
$user = exiger_utilisateur();
$titrePage = 'Mon espace';
$pageActive = 'espace';
require __DIR__ . '/../includes/header.php';

$stmt = db()->prepare('SELECT d.* FROM defauts d WHERE d.utilisateur_id = ? ORDER BY d.date_enregistrement DESC, d.id DESC');
$stmt->execute([$user['id']]);
$mesDefauts = $stmt->fetchAll();

$stmt = db()->prepare('SELECT f.nom FROM utilisateur_fonctions uf JOIN fonctions f ON f.id = uf.fonction_id WHERE uf.utilisateur_id = ?');
$stmt->execute([$user['id']]);
$mesFonctions = array_column($stmt->fetchAll(), 'nom');
?>
<h1 class="page">Mon espace — ma défauthèque personnelle</h1>
<?php if (isset($_GET['bienvenue'])): ?>
  <div class="succes">Compte créé : votre défauthèque personnelle est ouverte. Essai gratuit <?= DUREE_ESSAI_MOIS ?> mois, puis <?= ABONNEMENT_PRIX ?>.</div>
<?php endif; ?>
<p><strong><?= h($user['nom']) ?></strong>
  <?php if ($mesFonctions): ?> — fonctions : <?= h(implode(', ', $mesFonctions)) ?><?php endif; ?>
  <?php if ($user['equipe']): ?> — équipe : <?= h($user['equipe']) ?><?php endif; ?>
  — inscrit le <?= h($user['date_inscription']) ?>
  — abonnement : <strong><?= h($user['statut_abonnement']) ?></strong>
  <?php if ($user['statut_abonnement'] === 'essai'): ?>(essai : encore <?= jours_restants_essai($user) ?> jours gratuits)<?php endif; ?>
  <?php if ($user['date_expiration']): ?> — valable jusqu'au <?= h($user['date_expiration']) ?><?php endif; ?>
</p>
<p><a class="bouton" href="<?= BASE_URL ?>/defauts/nouveau.php">Enregistrer un nouveau défaut</a></p>

<h2>Mes défauts enregistrés</h2>
<?php if (!$mesDefauts): ?>
  <p>Aucun défaut enregistré pour le moment.</p>
<?php else: ?>
<div class="liste-defauts">
  <?php foreach ($mesDefauts as $d): ?>
    <a class="carte-defaut" href="<?= BASE_URL ?>/defauts/fiche.php?id=<?= (int)$d['id'] ?>">
      <?php if ($d['photo1']): ?>
        <img src="<?= h(url_fichier($d['photo1'])) ?>" alt="Photo du défaut">
      <?php endif; ?>
      <div class="corps">
        <h3><?= h(mb_strimwidth($d['defaut_constate'] ?? 'Défaut sans titre', 0, 80, '…')) ?></h3>
        <span class="date"><?= h($d['date_enregistrement']) ?> <?= h($d['heure_enregistrement']) ?></span>
      </div>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
