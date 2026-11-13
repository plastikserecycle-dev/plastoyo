<?php
require __DIR__ . '/../config/config.php';
exiger_admin();

// CRUD générique des tables d'information renseignées par l'administrateur.
$CONFIG = [
    'matiere'     => ['titre' => 'Matière',    'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'famille'     => ['titre' => 'Familles',   'colonnes' => ['famille' => 'Famille', 'abreviation' => 'Abréviation', 'fusion' => 'Fusion', 'temperature_moule' => 'Température moule', 'etuvage' => 'Étuvage', 'pourcentage_recyclage' => '% de recyclage', 'fluidite_chaude' => 'Fluidité à chaud', 'retrait_moulage' => 'Retrait de moulage', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'moule'       => ['titre' => 'Moule',      'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'machine'     => ['titre' => 'Machine',    'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'materiel'    => ['titre' => 'Matériel',   'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'montage'     => ['titre' => 'Montage',    'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'reglage'     => ['titre' => 'Réglage',    'colonnes' => ['identifiant' => 'Identification du réglage', 'valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation']],
    'mode'        => ['titre' => 'Mode',       'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['photo' => 'Photo', 'video' => 'Vidéo de formation']],
    'processus'   => ['titre' => 'Processus',  'colonnes' => ['phase' => 'Phase', 'valeur' => 'Valeur', 'explication' => 'Explication'], 'fichiers' => ['image' => 'Image', 'video' => 'Vidéo de formation'], 'phases' => ['Préparation', 'Montage moule', 'Réglages', 'Démarrage', 'Stabilisation cycle', 'Modification']],
    'defaut_info' => ['titre' => 'Défaut',     'colonnes' => ['valeur' => 'Valeur', 'explication' => 'Texte d\'explication'], 'fichiers' => ['video' => 'Vidéo d\'autoformation']],
    'cause'       => ['titre' => 'Causes',     'colonnes' => ['categorie' => 'Catégorie', 'valeur' => 'Cause', 'explication' => 'Texte explicatif (fenêtre avant coche)'], 'fichiers' => ['video' => 'Vidéo'], 'categories' => ['matiere' => 'Matière', 'moule' => 'Moule', 'machine' => 'Machine', 'materiel' => 'Matériel', 'montage' => 'Montage', 'mode' => 'Mode']],
    'remede'      => ['titre' => 'Remèdes',    'colonnes' => ['categorie' => 'Catégorie', 'valeur' => 'Remède', 'explication' => 'Explication'], 'fichiers' => ['video' => 'Vidéo'], 'categories' => ['matiere' => 'Matière', 'moule' => 'Moule', 'machine' => 'Machine', 'materiel' => 'Matériel', 'montage' => 'Montage', 'mode' => 'Mode']],
];

$table = $_GET['table'] ?? $_POST['table'] ?? '';
if (!isset($CONFIG[$table])) {
    http_response_code(404);
    exit('Table inconnue');
}
$cfg = $CONFIG[$table];
$titrePage = 'Admin — ' . $cfg['titre'];
$pageActive = 'admin';

// Suppression
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $stmt = db()->prepare('DELETE FROM ' . $table . ' WHERE id = ?');
    $stmt->execute([(int)$_POST['id']]);
    header('Location: ' . BASE_URL . '/admin/table.php?table=' . $table);
    exit;
}

// Enregistrement (ajout / modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'save') {
    $id = (int)($_POST['id'] ?? 0);
    $colonnes = array_keys($cfg['colonnes']);
    $valeurs = [];
    foreach ($colonnes as $c) {
        $valeurs[$c] = trim($_POST[$c] ?? '');
    }
    foreach ($cfg['fichiers'] as $champ => $label) {
        $ext = ($champ === 'video') ? ['mp4', 'webm', 'ogg'] : ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $up = upload_fichier($_FILES['fichier_' . $champ] ?? ['name' => '', 'error' => UPLOAD_ERR_NO_FILE], $champ === 'video' ? 'videos' : 'images', $ext);
        if ($up !== null) {
            $valeurs[$champ] = $up;
        } elseif ($id > 0) {
            $stmt = db()->prepare('SELECT ' . $champ . ' FROM ' . $table . ' WHERE id = ?');
            $stmt->execute([$id]);
            $valeurs[$champ] = $stmt->fetchColumn() ?: null;
        } else {
            $valeurs[$champ] = null;
        }
    }
    $toutes = array_merge($colonnes, array_keys($cfg['fichiers']));
    if ($id > 0) {
        $set = implode(',', array_map(fn($c) => "$c = ?", $toutes));
        $stmt = db()->prepare("UPDATE $table SET $set WHERE id = ?");
        $stmt->execute(array_merge(array_values($valeurs), [$id]));
    } else {
        $stmt = db()->prepare('INSERT INTO ' . $table . ' (' . implode(',', $toutes) . ') VALUES (' . implode(',', array_fill(0, count($toutes), '?')) . ')');
        $stmt->execute(array_values($valeurs));
    }
    header('Location: ' . BASE_URL . '/admin/table.php?table=' . $table);
    exit;
}

// Édition : chargement de la ligne
$ligne = null;
if (($_GET['action'] ?? '') === 'edit') {
    $stmt = db()->prepare('SELECT * FROM ' . $table . ' WHERE id = ?');
    $stmt->execute([(int)$_GET['id']]);
    $ligne = $stmt->fetch();
}

require __DIR__ . '/../includes/header.php';
echo '<h1 class="page">Admin — ' . h($cfg['titre']) . '</h1>';
echo '<p><a href="' . BASE_URL . '/admin/index.php">← Tableau de bord</a></p>';

if ($ligne !== null || ($_GET['action'] ?? '') === 'new') {
    $ligne = $ligne ?? [];
    echo '<form class="formulaire" method="post" enctype="multipart/form-data">';
    echo '<input type="hidden" name="action" value="save"><input type="hidden" name="table" value="' . h($table) . '">';
    echo '<input type="hidden" name="id" value="' . (int)($ligne['id'] ?? 0) . '">';
    foreach ($cfg['colonnes'] as $c => $label) {
        echo '<label for="' . h($c) . '">' . h($label) . '</label>';
        if (isset($cfg['categories']) && $c === 'categorie') {
            echo '<select id="' . h($c) . '" name="' . h($c) . '">';
            foreach ($cfg['categories'] as $v => $n) {
                echo '<option value="' . h($v) . '"' . (($ligne[$c] ?? '') === $v ? ' selected' : '') . '>' . h($n) . '</option>';
            }
            echo '</select>';
        } elseif (isset($cfg['phases']) && $c === 'phase') {
            echo '<select id="' . h($c) . '" name="' . h($c) . '">';
            foreach ($cfg['phases'] as $v) {
                echo '<option value="' . h($v) . '"' . (($ligne[$c] ?? '') === $v ? ' selected' : '') . '>' . h($v) . '</option>';
            }
            echo '</select>';
        } elseif (in_array($c, ['explication'], true)) {
            echo '<textarea id="' . h($c) . '" name="' . h($c) . '">' . h($ligne[$c] ?? '') . '</textarea>';
        } else {
            echo '<input type="text" id="' . h($c) . '" name="' . h($c) . '" value="' . h($ligne[$c] ?? '') . '">';
        }
    }
    foreach ($cfg['fichiers'] as $champ => $label) {
        echo '<label>' . h($label) . '</label>';
        if (!empty($ligne[$champ])) {
            echo '<p class="fichier-actuel">Fichier actuel : <a href="' . h(url_fichier($ligne[$champ])) . '" target="_blank">' . h(basename($ligne[$champ])) . '</a></p>';
        }
        echo '<input type="file" name="fichier_' . h($champ) . '">';
    }
    echo '<p><button class="bouton" type="submit">Enregistrer</button> <a class="bouton bouton-secondaire" href="' . BASE_URL . '/admin/table.php?table=' . h($table) . '">Annuler</a></p>';
    echo '</form>';
    require __DIR__ . '/../includes/footer.php';
    exit;
}

$rows = db()->query('SELECT * FROM ' . $table . ' ORDER BY id')->fetchAll();
echo '<p><a class="bouton" href="' . BASE_URL . '/admin/table.php?table=' . h($table) . '&action=new">+ Ajouter une valeur</a></p>';
if (!$rows) {
    echo '<p>Aucune valeur pour le moment.</p>';
} else {
    echo '<table class="donnees"><tr><th>ID</th>';
    foreach ($cfg['colonnes'] as $label) echo '<th>' . h($label) . '</th>';
    echo '<th>Fichiers</th><th>Actions</th></tr>';
    foreach ($rows as $r) {
        echo '<tr><td>' . (int)$r['id'] . '</td>';
        foreach (array_keys($cfg['colonnes']) as $c) {
            echo '<td>' . h(mb_strimwidth((string)($r[$c] ?? ''), 0, 60, '…')) . '</td>';
        }
        echo '<td>';
        foreach ($cfg['fichiers'] as $champ => $label) {
            if (!empty($r[$champ])) echo h($label) . ': <a href="' . h(url_fichier($r[$champ])) . '" target="_blank">voir</a><br>';
        }
        echo '</td><td>';
        echo '<a href="' . BASE_URL . '/admin/table.php?table=' . h($table) . '&action=edit&id=' . (int)$r['id'] . '">Modifier</a> ';
        echo '<form method="post" style="display:inline" onsubmit="return confirm(\'Supprimer cette valeur ?\')">'
            . '<input type="hidden" name="action" value="delete"><input type="hidden" name="table" value="' . h($table) . '">'
            . '<input type="hidden" name="id" value="' . (int)$r['id'] . '"><button class="bouton bouton-secondaire" type="submit">Supprimer</button></form>';
        echo '</td></tr>';
    }
    echo '</table>';
}
require __DIR__ . '/../includes/footer.php';
