<?php
// Fonctions de rendu des pages d'information du process.

function carte_media(array $row, string $champImage = 'image'): string {
    $html = '<div class="carte-info"><h3>' . h($row['valeur']) . '</h3>';
    if (!empty($row['explication'])) {
        $html .= '<p>' . nl2br(h($row['explication'])) . '</p>';
    }
    $img = $row[$champImage] ?? null;
    $video = $row['video'] ?? null;
    if ($img || $video) {
        $html .= '<div class="media">';
        if ($img) {
            $html .= '<a href="' . h(url_fichier($img)) . '" target="_blank"><img src="' . h(url_fichier($img)) . '" alt="' . h($row['valeur']) . '"></a>';
        }
        if ($video) {
            $html .= '<video controls src="' . h(url_fichier($video)) . '"></video>';
        }
        $html .= '</div>';
    }
    $html .= '</div>';
    return $html;
}

function page_liste_simple(string $titre, string $table, string $pageActive, string $champImage = 'image'): void {
    $titrePage = $titre;
    require __DIR__ . '/header.php';
    echo '<h1 class="page">' . h($titre) . '</h1>';
    $rows = db()->query('SELECT * FROM ' . $table . ' ORDER BY id')->fetchAll();
    if (!$rows) {
        echo '<p>Aucune valeur enregistrée pour le moment.</p>';
    }
    foreach ($rows as $row) {
        if ($table === 'reglage') {
            $row['valeur'] = $row['identifiant'] . ' — ' . $row['valeur'];
        }
        echo carte_media($row, $champImage);
    }
    require __DIR__ . '/footer.php';
}

function page_groupee(string $titre, string $table, string $champGroupe, string $pageActive, array $ordreGroupes = [], string $champImage = 'image'): void {
    $titrePage = $titre;
    require __DIR__ . '/header.php';
    echo '<h1 class="page">' . h($titre) . '</h1>';
    $rows = db()->query('SELECT * FROM ' . $table . ' ORDER BY ' . $champGroupe . ', id')->fetchAll();
    if (!$rows) {
        echo '<p>Aucune valeur enregistrée pour le moment.</p>';
    }
    usort($rows, function ($a, $b) use ($champGroupe, $ordreGroupes) {
        $ia = array_search($a[$champGroupe], $ordreGroupes, true);
        $ib = array_search($b[$champGroupe], $ordreGroupes, true);
        $ia = $ia === false ? 999 : $ia;
        $ib = $ib === false ? 999 : $ib;
        return [$ia, $a['id']] <=> [$ib, $b['id']];
    });
    $groupe = null;
    foreach ($rows as $row) {
        if ($row[$champGroupe] !== $groupe) {
            if ($groupe !== null) echo '</div>';
            $groupe = $row[$champGroupe];
            echo '<h2>' . h(ucfirst($groupe)) . '</h2><div class="groupe-causes">';
        }
        if ($champImage === null) {
            echo '<div class="carte-info"><span class="badge-categorie">' . h($groupe) . '</span><h3>' . h($row['valeur']) . '</h3>';
            if (!empty($row['explication'])) echo '<p>' . nl2br(h($row['explication'])) . '</p>';
            if (!empty($row['video'])) echo '<video controls src="' . h(url_fichier($row['video'])) . '"></video>';
            echo '</div>';
        } else {
            echo carte_media($row, $champImage);
        }
    }
    if ($groupe !== null) echo '</div>';
    require __DIR__ . '/footer.php';
}
