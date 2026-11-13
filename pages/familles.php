<?php
require __DIR__ . '/../config/config.php';
$titrePage = 'Familles de matières';
$pageActive = 'familles';
require __DIR__ . '/../includes/header.php';

$rows = db()->query('SELECT * FROM famille ORDER BY famille')->fetchAll();
$colonnes = [
    'famille' => 'Famille', 'abreviation' => 'Abréviation', 'fusion' => 'Fusion',
    'temperature_moule' => 'Température moule', 'etuvage' => 'Étuvage',
    'pourcentage_recyclage' => '% de recyclage', 'fluidite_chaude' => 'Fluidité à chaud',
    'retrait_moulage' => 'Retrait de moulage',
];
?>
<h1 class="page">Familles de matières et conditions de transformation</h1>
<?php if (!$rows): ?>
  <p>Aucune famille enregistrée pour le moment.</p>
<?php else: ?>
<table class="donnees">
  <tr><th>Famille</th><th>Abrév.</th><th>Fusion</th><th>Temp. moule</th><th>Étuvage</th><th>% recyclage</th><th>Fluidité à chaud</th><th>Retrait</th><th>Explication</th></tr>
  <?php foreach ($rows as $r): ?>
  <tr>
    <td><strong><?= h($r['famille']) ?></strong></td>
    <td><?= h($r['abreviation']) ?></td>
    <td><?= h($r['fusion']) ?></td>
    <td><?= h($r['temperature_moule']) ?></td>
    <td><?= h($r['etuvage']) ?></td>
    <td><?= h($r['pourcentage_recyclage']) ?></td>
    <td><?= h($r['fluidite_chaude']) ?></td>
    <td><?= h($r['retrait_moulage']) ?></td>
    <td><?= nl2br(h($r['explication'])) ?>
      <?php if ($r['image']): ?><br><a href="<?= h(url_fichier($r['image'])) ?>" target="_blank">Image</a><?php endif; ?>
      <?php if ($r['video']): ?> — <a href="<?= h(url_fichier($r['video'])) ?>" target="_blank">Vidéo de formation</a><?php endif; ?>
    </td>
  </tr>
  <?php endforeach; ?>
</table>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
