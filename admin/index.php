<?php
require __DIR__ . '/../config/config.php';
$admin = exiger_admin();
$titrePage = 'Administration';
$pageActive = 'admin';
require __DIR__ . '/../includes/header.php';

$nbUsers = (int)db()->query('SELECT COUNT(*) FROM utilisateurs')->fetchColumn();
$nbDefautsPlastoyo = (int)db()->query("SELECT COUNT(*) FROM defauts WHERE origine='plastoyo'")->fetchColumn();
$nbDefautsOperateurs = (int)db()->query("SELECT COUNT(*) FROM defauts WHERE origine='operateur'")->fetchColumn();
$nbTransferts = (int)db()->query("SELECT COUNT(*) FROM transferts WHERE statut='en_attente'")->fetchColumn();
?>
<h1 class="page">Administration plastoyo — <?= h($admin['nom']) ?></h1>
<p><a href="<?= BASE_URL ?>/admin/deconnexion.php">Déconnexion admin</a></p>

<div class="actions-admin">
  <a href="<?= BASE_URL ?>/admin/table.php?table=matiere">Matière</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=famille">Familles</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=moule">Moule</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=machine">Machine</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=materiel">Matériel</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=montage">Montage</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=reglage">Réglage</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=mode">Mode</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=processus">Processus</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=defaut_info">Défaut</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=cause">Causes</a>
  <a href="<?= BASE_URL ?>/admin/table.php?table=remede">Remèdes</a>
</div>
<div class="actions-admin">
  <a href="<?= BASE_URL ?>/admin/defauts.php">Défauthèque plastoyo & défauts opérateurs (<?= $nbDefautsPlastoyo ?> + <?= $nbDefautsOperateurs ?>)</a>
  <a href="<?= BASE_URL ?>/admin/defaut_form.php">+ Nouveau défaut plastoyo</a>
  <a href="<?= BASE_URL ?>/admin/utilisateurs.php">Comptes opérateurs & abonnements (<?= $nbUsers ?>)</a>
  <?php if ($nbTransferts > 0): ?><a href="<?= BASE_URL ?>/admin/defauts.php">⚠ <?= $nbTransferts ?> demande(s) de transfert en attente</a><?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
