<?php
session_start();

require_once __DIR__ . '/../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: register.php');
    exit;
}

if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Jeton de securite invalide. Veuillez reessayer.';
    header('Location: register.php');
    exit;
}

$pseudo = trim($_POST['pseudo'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

$errors = [];

if (empty($pseudo)) {
    $errors[] = 'Le nom d\'utilisateur est obligatoire.';
} elseif (strlen($pseudo) < 3 || strlen($pseudo) > 50) {
    $errors[] = 'Le pseudo doit faire entre 3 et 50 caracteres.';
} elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $pseudo)) {
    $errors[] = 'Le pseudo ne peut contenir que des lettres, chiffres et _.';
}

if (empty($email)) {
    $errors[] = 'L\'adresse email est obligatoire.';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Veuillez entrer une adresse email valide.';
}

if (empty($password)) {
    $errors[] = 'Le mot de passe est obligatoire.';
} elseif (strlen($password) < 8) {
    $errors[] = 'Le mot de passe doit contenir au moins 8 caracteres.';
}

if ($password !== $confirm_password) {
    $errors[] = 'Les mots de passe ne correspondent pas.';
}

if (!empty($errors)) {
    $_SESSION['error'] = implode(' ', $errors);
    header('Location: register.php');
    exit;
}

try {
    $pdo = Database::getConnection();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Cette adresse email est deja utilisee.';
        header('Location: register.php');
        exit;
    }

    $stmt = $pdo->prepare("SELECT id FROM users WHERE pseudo = ?");
    $stmt->execute([$pseudo]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = 'Ce nom d\'utilisateur est deja pris.';
        header('Location: register.php');
        exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        INSERT INTO users (email, password, pseudo, role, credits, created_at)
        VALUES (?, ?, ?, 'user', 20.00, NOW())
    ");
    $stmt->execute([$email, $hashed_password, $pseudo]);

    $_SESSION['success'] = 'Inscription reussie ! Vous pouvez maintenant vous connecter.';
    header('Location: login.php');
    exit;

} catch (PDOException $e) {
    error_log('Erreur inscription: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors de l\'inscription. Veuillez reessayer plus tard.';
    header('Location: register.php');
    exit;
}
