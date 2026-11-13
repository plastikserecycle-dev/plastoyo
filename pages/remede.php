<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/rendu.php';
$ordre = ['matiere', 'moule', 'machine', 'materiel', 'montage', 'mode'];
page_groupee('Remèdes', 'remede', 'categorie', 'remede', $ordre, null);
