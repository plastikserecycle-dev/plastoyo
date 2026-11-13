<?php
require __DIR__ . '/config/config.php';
$titrePage = 'Accueil';
$pageActive = 'accueil';
require __DIR__ . '/includes/header.php';
?>
<div class="hero">
  <h1>Plastoyo — l'aide des opérateurs d'injection</h1>
  <p>Un défaut apparaît sur votre poste ? Naviguez dans les pages du process pour le comprendre,
     consultez la défauthèque des défauts déjà résolus, ou enregistrez votre propre défaut
     dans votre défauthèque personnelle pour le résoudre plus vite.</p>
</div>

<div class="choix-accueil">
  <a class="carte-choix" href="<?= BASE_URL ?>/pages/matiere.php">
    <h2>1. Pages d'information du process</h2>
    <p>Matière, familles, moule, machine, matériel, montage, mode, processus, défaut, cause et remède :
       explications, images et vidéos d'autoformation, accessibles librement.</p>
  </a>
  <a class="carte-choix" href="<?= BASE_URL ?>/defautheque/index.php">
    <h2>2. Défauthèque générale Plastoyo</h2>
    <p>Consultez gratuitement les défauts déjà enregistrés par l'équipe plastoyo : photos, réglages,
       causes cochées, commentaires et réponses. <?= DUREE_ESSAI_MOIS ?> mois gratuits puis <?= ABONNEMENT_PRIX ?>.</p>
  </a>
  <a class="carte-choix" href="<?= BASE_URL ?>/defauts/nouveau.php">
    <h2>3. Enregistrer un défaut</h2>
    <p>Créez votre compte pour ouvrir votre défauthèque personnelle et remplir le questionnaire
       d'enregistrement du défaut (photos, réglages, causes avec explications, commentaire).</p>
  </a>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
