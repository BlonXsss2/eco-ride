<?php
require_once __DIR__ . '/../src/config/session.php';

// on detruit la session
$_SESSION = [];

if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

session_destroy();

// on demarre une nouvelle session pour le message
session_start();
$_SESSION['success'] = 'Vous avez ete deconnecte avec succes.';

header('Location: index.php');
exit;
