<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/Booking.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter pour reserver un covoiturage.';
    header('Location: login.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
    $_SESSION['error'] = 'Requete invalide. Veuillez reessayer.';
    header('Location: index.php');
    exit;
}

$carpoolId = filter_var($_POST['carpool_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$carpoolId) {
    $_SESSION['error'] = 'Covoiturage invalide.';
    header('Location: index.php');
    exit;
}

$userId = (int)$_SESSION['user_id'];

$bookingModel = new Booking();
$result = $bookingModel->createBooking($carpoolId, $userId);

if ($result['success']) {
    $_SESSION['user_credits'] = $result['new_credits'];
    $_SESSION['success'] = 'Reservation confirmee ! 1 credit a ete deduit.';
} else {
    $_SESSION['error'] = $result['message'];
}

header('Location: carpool_details.php?id=' . $carpoolId);
exit;
