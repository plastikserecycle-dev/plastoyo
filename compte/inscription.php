<?php
require __DIR__ . '/../config/config.php';
$titrePage = 'Créer un compte';
$pageActive = 'inscription';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $equipe = trim($_POST['equipe'] ?? '');
    $fonctions = array_map('intval', $_POST['fonctions'] ?? []);

    if ($nom === '' || $email === '' || $mdp === '') {
        $erreur = 'Nom, email et mot de passe sont obligatoires.';
    } else {
        $stmt = db()->prepare('SELECT id FROM utilisateurs WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $erreur = 'Un compte existe déjà avec cet email.';
        } else {
            db()->beginTransaction();
            $stmt = db()->prepare('INSERT INTO utilisateurs (nom, email, mot_de_passe, equipe) VALUES (?, ?, ?, ?)');
            $stmt->execute([$nom, $email, password_hash($mdp, PASSWORD_DEFAULT), $equipe]);
            $uid = (int)db()->lastInsertId();
            $stmtF = db()->prepare('INSERT INTO utilisateur_fonctions (utilisateur_id, fonction_id) VALUES (?, ?)');
            foreach ($fonctions as $fid) {
                if ($fid > 0) $stmtF->execute([$uid, $fid]);
            }
            db()->commit();
            session_start_once();
            $_SESSION['utilisateur_id'] = $uid;
            header('Location: ' . BASE_URL . '/compte/espace.php?bienvenue=1');
            exit;
        }
    }
}

$fonctions = db()->query('SELECT * FROM fonctions ORDER BY id')->fetchAll();
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page">Créer mon compte plastoyo</h1>
<p>La création du compte ouvre automatiquement <strong>votre défauthèque personnelle</strong>.
   Essai gratuit <?= DUREE_ESSAI_MOIS ?> mois, puis abonnement <?= ABONNEMENT_PRIX ?>.</p>
<?php if ($erreur): ?><div class="erreur"><?= h($erreur) ?></div><?php endif; ?>
<form class="formulaire" method="post">
  <label for="nom">Nom de la personne *</label>
  <input type="text" id="nom" name="nom" required value="<?= h($_POST['nom'] ?? '') ?>">
  <label for="email">Email *</label>
  <input type="email" id="email" name="email" required value="<?= h($_POST['email'] ?? '') ?>">
  <label for="mot_de_passe">Mot de passe *</label>
  <input type="password" id="mot_de_passe" name="mot_de_passe" required>
  <label for="equipe">Équipe</label>
  <input type="text" id="equipe" name="equipe" value="<?= h($_POST['equipe'] ?? '') ?>">
  <fieldset>
    <legend>Fonction de la personne (à cocher)</legend>
    <?php foreach ($fonctions as $f): ?>
      <label><input type="checkbox" name="fonctions[]" value="<?= (int)$f['id'] ?>"> <?= h($f['nom']) ?></label>
    <?php endforeach; ?>
  </fieldset>
  <button class="bouton" type="submit">Créer mon compte et ma défauthèque</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
