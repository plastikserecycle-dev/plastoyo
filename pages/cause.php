<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/rendu.php';
$ordre = ['matiere', 'moule', 'machine', 'materiel', 'montage', 'mode'];
page_groupee('Causes des défauts', 'cause', 'categorie', 'cause', $ordre, 'image');
