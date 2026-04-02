<?php
// Recherche de covoiturages - traitement du formulaire

require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/Carpool.php';
require_once __DIR__ . '/../src/nosql/SearchHistory.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Location: index.php');
    exit;
}

$from = trim($_GET['from_city'] ?? '');
$to   = trim($_GET['to_city'] ?? '');
$date = trim($_GET['date'] ?? '');

$errors = [];

if (empty($from)) {
    $errors[] = 'La ville de depart est obligatoire';
}
if (empty($to)) {
    $errors[] = 'La ville d\'arrivee est obligatoire';
}
if (!empty($date) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
    $errors[] = 'Format de date invalide';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode('. ', $errors);
    header('Location: index.php');
    exit;
}

// recuperer les filtres
$filters = [];
if (!empty($_GET['eco_only'])) {
    $filters['eco_only'] = $_GET['eco_only'];
}
if (!empty($_GET['max_price'])) {
    $filters['max_price'] = filter_var($_GET['max_price'], FILTER_VALIDATE_FLOAT);
}
if (!empty($_GET['min_rating'])) {
    $filters['min_rating'] = filter_var($_GET['min_rating'], FILTER_VALIDATE_INT);
}

$carpoolModel = new Carpool();
$results = $carpoolModel->searchCarpools($from, $to, $date ?: null, $filters);

// on garde un petit historique (fichier json)
try {
    $history = new SearchHistory();
    $history->add($from, $to, $date, $filters);
} catch (Exception $e) {
    // tant pis, on bloque pas la recherche
}

// on stocke en session pour la page de resultats
$_SESSION['search_results'] = $results;
$_SESSION['search_params'] = [
    'from'    => $from,
    'to'      => $to,
    'date'    => $date,
    'filters' => $filters
];

header('Location: carpools.php');
exit;
