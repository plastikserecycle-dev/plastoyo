<?php
require __DIR__ . '/../config/config.php';
session_start_once();

// Premier démarrage : création automatique du compte administrateur
// admin@plastoyo.fr / admin123  (à changer immédiatement dans la table administrateurs)
if ((int)db()->query('SELECT COUNT(*) FROM administrateurs')->fetchColumn() === 0) {
    db()->prepare('INSERT INTO administrateurs (nom, email, mot_de_passe) VALUES (?, ?, ?)')
        ->execute(['Administrateur plastoyo', 'admin@plastoyo.fr', password_hash('admin123', PASSWORD_DEFAULT)]);
}

$erreur = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mdp = $_POST['mot_de_passe'] ?? '';
    $stmt = db()->prepare('SELECT * FROM administrateurs WHERE email = ?');
    $stmt->execute([$email]);
    $a = $stmt->fetch();
    if ($a && password_verify($mdp, $a['mot_de_passe'])) {
        $_SESSION['admin_id'] = (int)$a['id'];
        header('Location: ' . BASE_URL . '/admin/index.php');
        exit;
    }
    $erreur = 'Identifiants administrateur incorrects.';
}
$titrePage = 'Administration — Connexion';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page">Administration plastoyo</h1>
<?php if ($erreur): ?><div class="erreur"><?= h($erreur) ?></div><?php endif; ?>
<form class="formulaire" method="post">
  <label for="email">Email administrateur</label>
  <input type="email" id="email" name="email" required>
  <label for="mot_de_passe">Mot de passe</label>
  <input type="password" id="mot_de_passe" name="mot_de_passe" required>
  <button class="bouton" type="submit">Se connecter</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
