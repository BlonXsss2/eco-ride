<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/User.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter pour modifier votre profil.';
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: profile.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Requete invalide. Veuillez reessayer.';
    header('Location: profile.php');
    exit;
}

$userModel = new User();
$userId = getUserId();
$action = $_POST['action'] ?? '';

try {
    if ($action === 'update_profile') {
        $pseudo = trim($_POST['pseudo'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleSelection = $_POST['role_selection'] ?? null;
        $smokingAllowed = isset($_POST['smoking_allowed']) ? 1 : 0;
        $petsAllowed = isset($_POST['pets_allowed']) ? 1 : 0;

        if (strlen($pseudo) < 3 || strlen($pseudo) > 50) {
            $_SESSION['error'] = 'Le pseudo doit faire entre 3 et 50 caracteres.';
            header('Location: profile.php');
            exit;
        }

        if (!preg_match('/^[a-zA-Z0-9_]+$/', $pseudo)) {
            $_SESSION['error'] = 'Le pseudo ne peut contenir que des lettres, chiffres et _.';
            header('Location: profile.php');
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'Veuillez entrer une adresse email valide.';
            header('Location: profile.php');
            exit;
        }

        if (!$userModel->isPseudoUnique($pseudo, $userId)) {
            $_SESSION['error'] = 'Ce nom d\'utilisateur est deja pris.';
            header('Location: profile.php');
            exit;
        }

        if (!$userModel->isEmailUnique($email, $userId)) {
            $_SESSION['error'] = 'Cette adresse email est deja utilisee.';
            header('Location: profile.php');
            exit;
        }

        $validRoles = ['driver', 'passenger', 'both', ''];
        if (!in_array($roleSelection, $validRoles)) {
            $roleSelection = null;
        }

        $userModel->updateProfile($userId, [
            'pseudo' => $pseudo,
            'email' => $email,
            'role_selection' => $roleSelection ?: null,
            'smoking_allowed' => $smokingAllowed,
            'pets_allowed' => $petsAllowed
        ]);

        $_SESSION['user_pseudo'] = $pseudo;
        $_SESSION['user_email'] = $email;
        $_SESSION['success'] = 'Profil mis a jour avec succes !';
        header('Location: profile.php');
        exit;

    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
            $_SESSION['error'] = 'Tous les champs de mot de passe sont obligatoires.';
            header('Location: profile.php');
            exit;
        }

        if (strlen($newPassword) < 8) {
            $_SESSION['error'] = 'Le nouveau mot de passe doit contenir au moins 8 caracteres.';
            header('Location: profile.php');
            exit;
        }

        if ($newPassword !== $confirmPassword) {
            $_SESSION['error'] = 'Les nouveaux mots de passe ne correspondent pas.';
            header('Location: profile.php');
            exit;
        }

        $currentHash = $userModel->getPasswordHash($userId);
        if (!password_verify($currentPassword, $currentHash)) {
            $_SESSION['error'] = 'Le mot de passe actuel est incorrect.';
            header('Location: profile.php');
            exit;
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $userModel->updatePassword($userId, $newHash);

        $_SESSION['success'] = 'Mot de passe modifie avec succes !';
        header('Location: profile.php');
        exit;

    } else {
        $_SESSION['error'] = 'Action invalide.';
        header('Location: profile.php');
        exit;
    }

} catch (PDOException $e) {
    error_log('Erreur profil: ' . $e->getMessage());
    $_SESSION['error'] = 'Une erreur est survenue. Veuillez reessayer.';
    header('Location: profile.php');
    exit;
}
