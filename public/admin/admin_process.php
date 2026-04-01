<?php
// Traitement des actions admin

require_once __DIR__ . '/../../src/config/session.php';
require_once __DIR__ . '/../../src/config/database.php';

// protection admin
if (!isLoggedIn() || getUserRole() !== 'admin') {
    $_SESSION['error'] = 'Acces refuse.';
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// verification CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Jeton de securite invalide.';
    header('Location: dashboard.php');
    exit;
}

$action = $_POST['action'] ?? '';
$pdo = Database::getConnection();

if ($action === 'create_employee') {

    $pseudo   = trim($_POST['pseudo'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($pseudo) || empty($email) || empty($password)) {
        $_SESSION['error'] = 'Tous les champs sont obligatoires.';
        header('Location: dashboard.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Adresse email invalide.';
        header('Location: dashboard.php');
        exit;
    }

    if (strlen($password) < 8) {
        $_SESSION['error'] = 'Le mot de passe doit contenir au moins 8 caracteres.';
        header('Location: dashboard.php');
        exit;
    }

    // verifier unicite
    $check = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR pseudo = ?");
    $check->execute([$email, $pseudo]);
    if ($check->fetchColumn() > 0) {
        $_SESSION['error'] = 'Email ou pseudo deja utilise.';
        header('Location: dashboard.php');
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO users (email, password, pseudo, role, credits) VALUES (?, ?, ?, 'employee', 0)");
    $insert->execute([$email, $hashedPassword, $pseudo]);

    $_SESSION['success'] = 'Compte employe "' . htmlspecialchars($pseudo) . '" cree avec succes.';

} elseif ($action === 'suspend') {

    $userId = (int)($_POST['user_id'] ?? 0);

    // on ne peut pas se suspendre soi-meme
    if ($userId <= 0 || $userId === getUserId()) {
        $_SESSION['error'] = 'Action invalide.';
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET suspended = 1, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Compte suspendu.';
    } else {
        $_SESSION['error'] = 'Utilisateur introuvable.';
    }

} elseif ($action === 'reactivate') {

    $userId = (int)($_POST['user_id'] ?? 0);

    if ($userId <= 0) {
        $_SESSION['error'] = 'Action invalide.';
        header('Location: dashboard.php');
        exit;
    }

    $stmt = $pdo->prepare("UPDATE users SET suspended = 0, updated_at = NOW() WHERE id = ?");
    $stmt->execute([$userId]);

    if ($stmt->rowCount() > 0) {
        $_SESSION['success'] = 'Compte reactive.';
    } else {
        $_SESSION['error'] = 'Utilisateur introuvable.';
    }

} else {
    $_SESSION['error'] = 'Action inconnue.';
}

header('Location: dashboard.php');
exit;
