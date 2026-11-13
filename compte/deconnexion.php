<?php
require __DIR__ . '/../config/config.php';
session_start_once();
unset($_SESSION['utilisateur_id']);
header('Location: ' . BASE_URL . '/index.php');
exit;
