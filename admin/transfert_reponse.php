<?php
require __DIR__ . '/../config/config.php';
// Réponse de l'entreprise au fichier temporaire de demande de transfert.
$token = $_GET['token'] ?? '';
$reponse = strtoupper(trim($_GET['reponse'] ?? ''));
$stmt = db()->prepare('SELECT * FROM transferts WHERE token = ?');
$stmt->execute([$token]);
$t = $stmt->fetch();

$titrePage = 'Réponse au transfert';
require __DIR__ . '/../includes/header.php';
if (!$t) {
    echo '<p>Lien de demande invalide.</p>';
} elseif (in_array($reponse, ['ACCEPTE', 'REFUSE'], true)) {
    db()->prepare("UPDATE transferts SET statut = ?, date_reponse = NOW() WHERE id = ?")
        ->execute([$reponse === 'ACCEPTE' ? 'accepte' : 'refuse', $t['id']]);
    if ($reponse === 'ACCEPTE') {
        // Transfert du défaut opérateur vers la défauthèque générale plastoyo
        db()->beginTransaction();
        $stmt = db()->prepare('SELECT * FROM defauts WHERE id = ?');
        $stmt->execute([$t['defaut_id']]);
        $d = $stmt->fetch();
        if ($d) {
            unset($d['id']);
            $d['origine'] = 'plastoyo';
            $cols = array_keys($d);
            db()->prepare('INSERT INTO defauts (' . implode(',', $cols) . ') VALUES (' . implode(',', array_fill(0, count($cols), '?')) . ')')
                ->execute(array_values($d));
            $nouveauId = (int)db()->lastInsertId();
            db()->prepare('INSERT INTO defaut_cause (defaut_id, cause_id) SELECT ?, cause_id FROM defaut_cause WHERE defaut_id = ?')
                ->execute([$nouveauId, $t['defaut_id']]);
            db()->prepare('INSERT INTO commentaires_defaut (defaut_id, auteur, est_admin, texte, parent_id) SELECT ?, auteur, est_admin, texte, NULL FROM commentaires_defaut WHERE defaut_id = ?')
                ->execute([$nouveauId, $t['defaut_id']]);
        }
        db()->commit();
        echo '<div class="succes">Accord reçu : le défaut n°' . (int)$t['defaut_id'] . ' a été transféré dans la défauthèque générale plastoyo.</div>';
    } else {
        echo '<div class="erreur">Transfert refusé par l\'entreprise : le défaut reste dans la défauthèque de l\'opérateur.</div>';
    }
    echo '<p><a href="' . BASE_URL . '/admin/defauts.php">Retour (admin)</a></p>';
} else {
    echo '<p>Demande de transfert du défaut n°' . (int)$t['defaut_id'] . ' (' . h($t['entreprise']) . ').</p>';
    echo '<p><a class="bouton" href="' . BASE_URL . '/admin/transfert_reponse.php?token=' . h($token) . '&reponse=ACCEPTE">J\'accepte le transfert dans la défauthèque plastoyo</a> ';
    echo '<a class="bouton bouton-secondaire" href="' . BASE_URL . '/admin/transfert_reponse.php?token=' . h($token) . '&reponse=REFUSE">Je refuse</a></p>';
}
require __DIR__ . '/../includes/footer.php';
