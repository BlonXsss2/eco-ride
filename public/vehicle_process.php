<?php
// Traitement des vehicules (ajout, modification, suppression)

require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Vehicle.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter pour gerer vos vehicules.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

// verification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Requete invalide. Veuillez reessayer.';
    header('Location: profile.php');
    exit;
}

$userModel = new User();
$vehicleModel = new Vehicle();
$userId = getUserId();
$user = $userModel->getUserById($userId);
$action = $_POST['action'] ?? '';

try {
    if ($action === 'add' || $action === 'update') {

        // verifier que l'utilisateur est conducteur
        if ($user['role_selection'] !== 'driver' && $user['role_selection'] !== 'both') {
            $_SESSION['error'] = 'Vous devez etre conducteur pour gerer des vehicules.';
            header('Location: profile.php');
            exit;
        }

        $brand = trim($_POST['brand'] ?? '');
        $model = trim($_POST['model'] ?? '');
        $energyType = $_POST['energy_type'] ?? '';
        $seats = (int)($_POST['seats'] ?? 0);
        $color = trim($_POST['color'] ?? '');
        $licensePlate = trim($_POST['license_plate'] ?? '');
        $registrationDate = trim($_POST['registration_date'] ?? '');

        // validation des champs obligatoires
        if (empty($brand) || empty($model) || empty($energyType) || $seats < 1) {
            $_SESSION['error'] = 'Veuillez remplir tous les champs obligatoires.';
            header('Location: vehicle_add.php' . ($action === 'update' ? '?edit=' . $_POST['vehicle_id'] : ''));
            exit;
        }

        if (strlen($brand) > 100 || strlen($model) > 100) {
            $_SESSION['error'] = 'La marque et le modele doivent faire moins de 100 caracteres.';
            header('Location: vehicle_add.php' . ($action === 'update' ? '?edit=' . $_POST['vehicle_id'] : ''));
            exit;
        }

        // verifier le type d'energie
        $validEnergyTypes = ['electric', 'hybrid', 'petrol', 'diesel', 'other'];
        if (!in_array($energyType, $validEnergyTypes)) {
            $_SESSION['error'] = 'Type d\'energie invalide.';
            header('Location: vehicle_add.php' . ($action === 'update' ? '?edit=' . $_POST['vehicle_id'] : ''));
            exit;
        }

        if ($seats < 1 || $seats > 8) {
            $_SESSION['error'] = 'Le nombre de places doit etre entre 1 et 8.';
            header('Location: vehicle_add.php' . ($action === 'update' ? '?edit=' . $_POST['vehicle_id'] : ''));
            exit;
        }

        $data = [
            'brand'             => $brand,
            'model'             => $model,
            'energy_type'       => $energyType,
            'seats'             => $seats,
            'color'             => $color ?: null,
            'license_plate'     => $licensePlate ?: null,
            'registration_date' => $registrationDate ?: null
        ];

        if ($action === 'add') {
            $vehicleModel->addVehicle($userId, $data);
            $_SESSION['success'] = 'Vehicule ajoute avec succes !';
        } else {
            $vehicleId = (int)($_POST['vehicle_id'] ?? 0);

            // verifier que le vehicule appartient a l'utilisateur
            $vehicle = $vehicleModel->getVehicleById($vehicleId);
            if (!$vehicle || $vehicle['user_id'] != $userId) {
                $_SESSION['error'] = 'Vehicule introuvable.';
                header('Location: profile.php');
                exit;
            }

            $vehicleModel->updateVehicle($vehicleId, $userId, $data);
            $_SESSION['success'] = 'Vehicule mis a jour avec succes !';
        }

        header('Location: profile.php');
        exit;

    } elseif ($action === 'delete') {
        $vehicleId = (int)($_POST['vehicle_id'] ?? 0);

        $deleted = $vehicleModel->deleteVehicle($vehicleId, $userId);
        if ($deleted) {
            $_SESSION['success'] = 'Vehicule supprime avec succes !';
        } else {
            $_SESSION['error'] = 'Impossible de supprimer le vehicule.';
        }

        header('Location: profile.php');
        exit;

    } else {
        $_SESSION['error'] = 'Action invalide.';
        header('Location: profile.php');
        exit;
    }

} catch (PDOException $e) {
    error_log('Erreur vehicule: ' . $e->getMessage());

    if (strpos($e->getMessage(), 'Duplicate entry') !== false && strpos($e->getMessage(), 'license_plate') !== false) {
        $_SESSION['error'] = 'Cette immatriculation est deja enregistree.';
    } else {
        $_SESSION['error'] = 'Une erreur est survenue. Veuillez reessayer.';
    }

    header('Location: vehicle_add.php' . ($action === 'update' ? '?edit=' . ($_POST['vehicle_id'] ?? '') : ''));
    exit;
}
