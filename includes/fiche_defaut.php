<?php
// Affiche la fiche complète d'un défaut :
// - toutes les lignes descriptives (même vides)
// - puis seulement les causes cochées, groupées par catégorie
// - puis le commentaire et les réponses nominatives
function afficher_fiche_defaut(array $d, array $causes, array $commentaires): void {
    $champs = [
        'date_enregistrement' => 'Date',
        'nom_personne' => 'Nom de la personne',
        'equipe' => 'Équipe d\'enregistrement',
        'heure_enregistrement' => 'Heure d\'enregistrement',
        'defaut_constate' => 'Défaut constaté',
        'ref_matiere' => 'Référence matière',
        'famille_abbr' => 'Famille (abréviation)',
        'densite_matiere' => 'Densité de la matière',
        'retrait_moulage_fabricant' => 'Retrait de moulage (fabricant)',
        'temp_fusion_reglage' => 'Température de fusion du réglage',
        'etuvage_duree + etuvage_temperature' => 'Étuvage (durée / température)',
        'temp_moule_reglage' => 'Température du moule du réglage',
        'pourcentage_recyclage' => '% de recyclage (0 si aucun)',
        'nb_empreintes' => 'Nombre d\'empreintes du moule',
        'poids_matiere_injecter' => 'Poids de matière à injecter',
        'mode_alimentation' => 'Mode d\'alimentation',
        'epaisseur_min + epaisseur_max' => 'Épaisseurs mini / maxi d\'écoulement',
        'tonnage_presse' => 'Tonnage de la presse',
        'volume_injectable' => 'Volume injectable',
        'diametre_vis' => 'Diamètre de vis',
        'course_vis' => 'Course de la vis',
        'type_etuve' => 'Type d\'étuve installée',
        'volume_etuvage' => 'Volume de l\'étuvage',
        'type_regulateur' => 'Type de régulateur installé',
        'temperature_fusion' => 'Température de fusion',
        'temps_dosage' => 'Temps de dosage',
        'contrepression_dosage' => 'Contrepression de dosage',
        'conditions_etuvage' => 'Conditions d\'étuvage',
        'temperature_empreintes' => 'Température des empreintes',
        'temps_remplissage' => 'Temps de remplissage',
        'pression_commutation' => 'Pression de commutation',
        'pression_maintien' => 'Pression de maintien',
        'duree_maintien' => 'Durée de maintien',
        'temps_cycle' => 'Temps de cycle',
        'precision_valeur' => 'Précision',
        'poids_grappe' => 'Poids de la grappe',
        'equilibrage_remplissage' => 'Équilibrage du remplissage',
    ];
    echo '<div class="fiche-defaut">';
    echo '<h2>Fiche de défaut n°' . (int)$d['id'] . '</h2>';

    $photos = array_filter([$d['photo1'] ?? null, $d['photo2'] ?? null, $d['photo3'] ?? null]);
    if ($photos) {
        echo '<div class="photos-fiche">';
        foreach ($photos as $p) {
            echo '<a href="' . h(url_fichier($p)) . '" target="_blank"><img src="' . h(url_fichier($p)) . '" alt="Photo du défaut"></a>';
        }
        echo '</div>';
    }

    echo '<dl>';
    foreach ($champs as $cle => $label) {
        if (strpos($cle, ' + ') !== false) {
            [$a, $b] = explode(' + ', $cle);
            $valeur = trim(($d[$a] ?? '') . ' / ' . ($d[$b] ?? ''), ' /');
        } else {
            $valeur = $d[$cle] ?? '';
        }
        echo '<dt>' . h($label) . '</dt><dd>' . ($valeur !== '' ? nl2br(h($valeur)) : '<em>non renseigné</em>') . '</dd>';
    }
    echo '</dl>';

    if ($causes) {
        echo '<h3>Causes cochées</h3>';
        $parCategorie = [];
        foreach ($causes as $c) {
            $parCategorie[$c['categorie']][] = $c['valeur'];
        }
        $noms = ['matiere' => 'Matière', 'moule' => 'Moule', 'machine' => 'Machine',
                 'materiel' => 'Matériel', 'montage' => 'Montage', 'mode' => 'Mode'];
        foreach ($parCategorie as $cat => $valeurs) {
            echo '<div class="groupe-causes"><h3>' . h($noms[$cat] ?? ucfirst($cat)) . '</h3><ul>';
            foreach ($valeurs as $v) {
                echo '<li>' . h($v) . '</li>';
            }
            echo '</ul></div>';
        }
    }

    if (!empty($d['commentaire'])) {
        echo '<h3>Commentaire</h3><p>' . nl2br(h($d['commentaire'])) . '</p>';
    }

    echo '<div class="commentaires"><h3>Commentaires et réponses</h3>';
    if (!$commentaires) {
        echo '<p>Aucun commentaire pour le moment.</p>';
    }
    $parId = [];
    foreach ($commentaires as $c) { $parId[$c['id']] = $c + ['reponses' => []]; }
    $racines = [];
    foreach ($parId as $id => $c) {
        if ($c['parent_id'] && isset($parId[$c['parent_id']])) {
            $parId[$c['parent_id']]['reponses'][] = &$parId[$id];
        } else {
            $racines[] = &$parId[$id];
        }
    }
    $afficher = function (array $c, bool $reponse) use (&$afficher): void {
        echo '<div class="commentaire' . ($reponse ? ' reponse' : '') . '">';
        echo '<span class="auteur">' . h($c['auteur']) . ($c['est_admin'] ? ' (administrateur)' : '') . '</span> ';
        echo '<span class="date">' . h($c['date_creation']) . '</span>';
        echo '<p>' . nl2br(h($c['texte'])) . '</p></div>';
        foreach ($c['reponses'] as $r) { $afficher($r, true); }
    };
    foreach ($racines as $c) { $afficher($c, false); }
    echo '</div></div>';
}
