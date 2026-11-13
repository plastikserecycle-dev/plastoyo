<?php
// Configuration de connexion MySQL (XAMPP par défaut)
define('DB_HOST', 'localhost');
define('DB_NAME', 'plastoyo');
define('DB_USER', 'root');
define('DB_PASS', '');

define('BASE_URL', '/plastoyo');           // adapter si le dossier s'appelle autrement dans htdocs
define('UPLOAD_DIR', __DIR__ . '/../uploads');
define('UPLOAD_URL', BASE_URL . '/uploads');

define('ABONNEMENT_PRIX', '100 EUR/an');
define('DUREE_ESSAI_MOIS', 6);

function db(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
             PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
        );
    }
    return $pdo;
}

function h(?string $s): string {
    return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
}

function session_start_once(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function utilisateur_connecte(): ?array {
    session_start_once();
    if (empty($_SESSION['utilisateur_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM utilisateurs WHERE id = ?');
    $stmt->execute([$_SESSION['utilisateur_id']]);
    $u = $stmt->fetch();
    return $u ?: null;
}

function admin_connecte(): ?array {
    session_start_once();
    if (empty($_SESSION['admin_id'])) {
        return null;
    }
    $stmt = db()->prepare('SELECT * FROM administrateurs WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $a = $stmt->fetch();
    return $a ?: null;
}

function exiger_utilisateur(): array {
    $u = utilisateur_connecte();
    if (!$u) {
        header('Location: ' . BASE_URL . '/compte/connexion.php');
        exit;
    }
    return $u;
}

function exiger_admin(): array {
    $a = admin_connecte();
    if (!$a) {
        header('Location: ' . BASE_URL . '/admin/connexion.php');
        exit;
    }
    return $a;
}

// Accès à la défauthèque générale et à l'espace de création :
// gratuit pendant 6 mois après la création du compte, puis payant (100 EUR/an).
// Les visiteurs non inscrits ont un accès libre à la défauthèque publique.
function acces_defautheque_autorise(?array $user): bool {
    if (!$user) {
        return true;
    }
    $inscription = strtotime($user['date_inscription']);
    if ($inscription === false) {
        return true;
    }
    if (time() < strtotime('+' . DUREE_ESSAI_MOIS . ' months', $inscription)) {
        return true;
    }
    return $user['statut_abonnement'] === 'paye'
        && !empty($user['date_expiration'])
        && strtotime($user['date_expiration']) >= time();
}

function jours_restants_essai(array $user): int {
    $fin = strtotime('+' . DUREE_ESSAI_MOIS . ' months', strtotime($user['date_inscription']));
    return max(0, (int)ceil(($fin - time()) / 86400));
}

// Upload d'un fichier (image ou vidéo) vers uploads/<sous-dossier>
function upload_fichier(array $file, string $sousDossier, array $extensionsAutorisees): ?string {
    if (empty($file['name']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extensionsAutorisees, true)) {
        return null;
    }
    $nom = uniqid('plastoyo_', true) . '.' . $ext;
    $dest = UPLOAD_DIR . '/' . $sousDossier;
    if (!is_dir($dest)) {
        mkdir($dest, 0775, true);
    }
    if (move_uploaded_file($file['tmp_name'], $dest . '/' . $nom)) {
        return $sousDossier . '/' . $nom;
    }
    return null;
}

function url_fichier(?string $f): string {
    return $f ? UPLOAD_URL . '/' . rawurlencode($f) : '';
}

function champ_fichier(?string $existant, string $label): void {
    echo '<label>' . h($label) . '</label>';
    if ($existant) {
        echo '<p class="fichier-actuel">Fichier actuel : <a href="' . h(url_fichier($existant)) . '" target="_blank">' . h(basename($existant)) . '</a></p>';
    }
    echo '<input type="file" name="fichier">';
}
