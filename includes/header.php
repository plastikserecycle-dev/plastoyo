<?php
session_start_once();
$utilisateur = utilisateur_connecte();
$admin = admin_connecte();
$accesOK = acces_defautheque_autorise($utilisateur);
$pageActive = $pageActive ?? '';
function nav_active(string $page, string $active): string { return $page === $active ? ' class="active"' : ''; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= h($titrePage ?? 'Plastoyo') ?> - Plastoyo</title>
<link rel="stylesheet" href="<?= BASE_URL ?>/assets/css/style.css">
</head>
<body>
<header class="site-header">
  <a class="logo" href="<?= BASE_URL ?>/index.php">PLASTO<span>YO</span></a>
  <nav class="main-nav">
    <a href="<?= BASE_URL ?>/index.php"<?= nav_active('accueil', $pageActive) ?>>Accueil</a>
    <div class="nav-groupe">
      <span>Process</span>
      <div class="sous-menu">
        <a href="<?= BASE_URL ?>/pages/matiere.php">Matière</a>
        <a href="<?= BASE_URL ?>/pages/familles.php">Familles</a>
        <a href="<?= BASE_URL ?>/pages/moule.php">Moule</a>
        <a href="<?= BASE_URL ?>/pages/machine.php">Machine</a>
        <a href="<?= BASE_URL ?>/pages/materiel.php">Matériel</a>
        <a href="<?= BASE_URL ?>/pages/montage.php">Montage</a>
        <a href="<?= BASE_URL ?>/pages/mode.php">Mode</a>
        <a href="<?= BASE_URL ?>/pages/processus.php">Processus</a>
        <a href="<?= BASE_URL ?>/pages/defaut.php">Défaut</a>
        <a href="<?= BASE_URL ?>/pages/cause.php">Cause</a>
        <a href="<?= BASE_URL ?>/pages/remede.php">Remède</a>
      </div>
    </div>
    <a href="<?= BASE_URL ?>/defautheque/index.php"<?= nav_active('defautheque', $pageActive) ?>>Défauthèque Plastoyo<?= $accesOK ? '' : ' 🔒' ?></a>
    <?php if ($utilisateur): ?>
      <a href="<?= BASE_URL ?>/compte/espace.php"<?= nav_active('espace', $pageActive) ?>>Mon espace</a>
      <a href="<?= BASE_URL ?>/compte/deconnexion.php">Déconnexion (<?= h($utilisateur['nom']) ?>)</a>
    <?php else: ?>
      <a href="<?= BASE_URL ?>/compte/connexion.php"<?= nav_active('connexion', $pageActive) ?>>Connexion</a>
      <a href="<?= BASE_URL ?>/compte/inscription.php" class="bouton-inscription">Créer un compte</a>
    <?php endif; ?>
    <?php if ($admin): ?>
      <a href="<?= BASE_URL ?>/admin/index.php">Administration</a>
    <?php endif; ?>
  </nav>
</header>
<?php if ($utilisateur && !$accesOK): ?>
  <div class="bandeau-payant">
    Votre essai gratuit de <?= DUREE_ESSAI_MOIS ?> mois est terminé.
    L'accès à la défauthèque et à l'espace de création devient payant :
    <strong><?= ABONNEMENT_PRIX ?></strong>. Contactez l'administrateur plastoyo pour activer votre abonnement.
  </div>
<?php elseif ($utilisateur && $utilisateur['statut_abonnement'] === 'essai'): ?>
  <div class="bandeau-essai">
    Essai gratuit : encore <?= jours_restants_essai($utilisateur) ?> jours avant abonnement payant (<?= ABONNEMENT_PRIX ?>).
  </div>
<?php endif; ?>
<main class="contenu">
