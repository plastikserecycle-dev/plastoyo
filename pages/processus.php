<?php
require __DIR__ . '/../config/config.php';
require __DIR__ . '/../includes/rendu.php';
$ordre = ['Préparation', 'Montage moule', 'Réglages', 'Démarrage', 'Stabilisation cycle', 'Modification'];
page_groupee('Processus d\'injection', 'processus', 'phase', 'processus', $ordre);
