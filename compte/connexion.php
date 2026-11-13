<?php
require __DIR__ . '/../config/config.php';
$titrePage = 'Connexion';
$pageActive = 'connexion';
$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE email = ?');
    $stmt->execute([$email]);
    $u = $stmt->fetch();
    if ($u && password_verify($mdp, $u['mot_de_passe'])) {
        session_start_once();
        $_SESSION['utilisateur_id'] = (int)$u['id'];
        header('Location: ' . BASE_URL . '/compte/espace.php');
        exit;
    }
    $erreur = 'Email ou mot de passe incorrect.';
}
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page">Connexion</h1>
<?php if ($erreur): ?><div class="erreur"><?= h($erreur) ?></div><?php endif; ?>
<form class="formulaire" method="post">
  <label for="email">Email</label>
  <input type="email" id="email" name="email" required>
  <label for="mot_de_passe">Mot de passe</label>
  <input type="password" id="mot_de_passe" name="mot_de_passe" required>
  <button class="bouton" type="submit">Se connecter</button>
</form>
<p>Pas encore de compte ? <a href="<?= BASE_URL ?>/compte/inscription.php">Créez votre défauthèque personnelle</a>.</p>
<?php require __DIR__ . '/../includes/footer.php'; ?>
