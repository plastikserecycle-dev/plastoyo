<?php
require __DIR__ . '/../config/config.php';
exiger_admin();

// Suppression d'un défaut
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    db()->prepare('DELETE FROM defauts WHERE id = ?')->execute([(int)$_POST['id']]);
    header('Location: ' . BASE_URL . '/admin/defauts.php');
    exit;
}

// Demande de transfert d'un défaut opérateur vers la défauthèque plastoyo :
// l'admin renseigne l'entreprise, un fichier temporaire (token) est généré
// et envoyé à l'entreprise pour accord.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'transfert') {
    $did = (int)$_POST['defaut_id'];
    $entreprise = trim($_POST['entreprise'] ?? '');
    $email = trim($_POST['email_entreprise'] ?? '');
    $token = bin2hex(random_bytes(32));
    $fichier = 'transferts/demande_' . $token . '.txt';
    $contenu = "Demande d'accord de transfert dans la défauthèque générale plastoyo\n"
        . "Défaut n°$did — entreprise : $entreprise — contact : $email\n"
        . "Lien de réponse : " . BASE_URL . "/admin/transfert_reponse.php?token=$token\n"
        . "Répondez ACCEPTE ou REFUSE pour autoriser ou non le transfert du défaut.\n";
    file_put_contents(UPLOAD_DIR . '/' . $fichier, $contenu);
    db()->prepare('INSERT INTO transferts (defaut_id, entreprise, email_entreprise, token, fichier) VALUES (?, ?, ?, ?, ?)')
        ->execute([$did, $entreprise, $email, $token, $fichier]);
    header('Location: ' . BASE_URL . '/admin/defauts.php?transfert=1');
    exit;
}

$titrePage = 'Admin — Défauthèques';
$pageActive = 'admin';
require __DIR__ . '/../includes/header.php';
echo '<h1 class="page">Gestion des défauthèques</h1>';
echo '<p><a href="' . BASE_URL . '/admin/index.php">← Tableau de bord</a> — <a class="bouton" href="' . BASE_URL . '/admin/defaut_form.php">+ Nouveau défaut plastoyo</a></p>';
if (isset($_GET['transfert'])) {
    echo '<div class="succes">Demande de transfert envoyée à l\'entreprise : un fichier temporaire avec lien de réponse a été généré dans uploads/transferts/.</div>';
}

$defauts = db()->query("SELECT d.*, u.nom AS nom_utilisateur FROM defauts d LEFT JOIN utilisateurs u ON u.id = d.utilisateur_id ORDER BY d.id DESC")->fetchAll();
$transferts = db()->query('SELECT * FROM transferts ORDER BY date_demande DESC')->fetchAll();
$transfertParDefaut = [];
foreach ($transferts as $t) { $transfertParDefaut[$t['defaut_id']][] = $t; }

echo '<table class="donnees"><tr><th>ID</th><th>Origine</th><th>Défaut</th><th>Date</th><th>Opérateur</th><th>Transfert</th><th>Actions</th></tr>';
foreach ($defauts as $d) {
    echo '<tr><td>' . (int)$d['id'] . '</td>';
    echo '<td>' . ($d['origine'] === 'plastoyo' ? 'Plastoyo' : 'Opérateur') . '</td>';
    echo '<td>' . h(mb_strimwidth((string)$d['defaut_constate'], 0, 60, '…')) . '</td>';
    echo '<td>' . h($d['date_enregistrement']) . '</td>';
    echo '<td>' . h($d['nom_utilisateur'] ?? $d['nom_personne'] ?? '') . '</td>';
    echo '<td>';
    if (!empty($transfertParDefaut[$d['id']])) {
        foreach ($transfertParDefaut[$d['id']] as $t) {
            echo h($t['statut']) . ' (' . h($t['entreprise']) . ')<br>';
        }
    } elseif ($d['origine'] === 'operateur') {
        echo '<form method="post" style="display:inline-block">'
            . '<input type="hidden" name="action" value="transfert"><input type="hidden" name="defaut_id" value="' . (int)$d['id'] . '">'
            . '<input type="text" name="entreprise" placeholder="Entreprise" required style="width:110px">'
            . '<input type="email" name="email_entreprise" placeholder="Email entreprise" required style="width:150px">'
            . '<button class="bouton bouton-secondaire" type="submit">Demander l\'accord</button></form>';
    }
    echo '</td><td>';
    echo '<a href="' . BASE_URL . '/admin/defaut_view.php?id=' . (int)$d['id'] . '">Voir</a> ';
    if ($d['origine'] === 'plastoyo') {
        echo '<a href="' . BASE_URL . '/admin/defaut_form.php?id=' . (int)$d['id'] . '">Modifier</a> ';
    }
    echo '<form method="post" style="display:inline" onsubmit="return confirm(\'Supprimer ce défaut ?\')">'
        . '<input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="' . (int)$d['id'] . '">'
        . '<button class="bouton bouton-secondaire" type="submit">Supprimer</button></form>';
    echo '</td></tr>';
}
echo '</table>';
require __DIR__ . '/../includes/footer.php';
