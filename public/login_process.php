<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/config/database.php';

// on verifie que c'est bien un POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// verification du token CSRF
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    $_SESSION['error'] = 'Jeton de securite invalide. Veuillez reessayer.';
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

// verification des champs
if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Veuillez entrer votre email et mot de passe.';
    header('Location: login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = 'Veuillez entrer une adresse email valide.';
    header('Location: login.php');
    exit;
}

try {
    $pdo = Database::getConnection();

    // chercher l'utilisateur par email
    $stmt = $pdo->prepare("SELECT id, email, password, pseudo, role, credits, suspended FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        $_SESSION['error'] = 'Email ou mot de passe incorrect.';
        header('Location: login.php');
        exit;
    }

    // verifier si le compte est suspendu
    if ((int)$user['suspended'] === 1) {
        $_SESSION['error'] = 'Votre compte a ete suspendu. Contactez l\'administrateur.';
        header('Location: login.php');
        exit;
    }

    // regenerer la session (securite)
    regenerateSession();

    // stocker les infos en session
    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_pseudo'] = $user['pseudo'];
    $_SESSION['user_role'] = $user['role'];
    $_SESSION['user_credits'] = (float)$user['credits'];

    // nouveau token CSRF
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));

    // redirection selon le role
    if ($user['role'] === 'admin') {
        header('Location: admin/dashboard.php');
    } elseif ($user['role'] === 'employee') {
        header('Location: employee/dashboard.php');
    } else {
        header('Location: index.php');
    }
    exit;

} catch (PDOException $e) {
    error_log('Erreur login: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur de connexion. Veuillez reessayer plus tard.';
    header('Location: login.php');
    exit;
}
