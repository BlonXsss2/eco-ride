<?php
// Traitement des avis (approuver / rejeter)

require_once __DIR__ . '/../../src/config/session.php';
require_once __DIR__ . '/../../src/config/database.php';

// protection employe ou admin
if (!isLoggedIn() || !in_array(getUserRole(), ['employee', 'admin'])) {
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

$reviewId = (int)($_POST['review_id'] ?? 0);
$action = $_POST['action'] ?? '';

if ($reviewId <= 0 || !in_array($action, ['approve', 'reject'])) {
    $_SESSION['error'] = 'Requete invalide.';
    header('Location: dashboard.php');
    exit;
}

try {
    $pdo = Database::getConnection();
    $employeeId = getUserId();

    if ($action === 'approve') {
        $sql = "UPDATE reviews 
                SET validated = 1, rejected = 0, validated_by = :emp_id, validated_at = NOW() 
                WHERE id = :id AND validated = 0";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':emp_id' => $employeeId, ':id' => $reviewId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Avis approuve avec succes.';
        } else {
            $_SESSION['error'] = 'Avis introuvable ou deja traite.';
        }

    } elseif ($action === 'reject') {
        $sql = "UPDATE reviews 
                SET rejected = 1, validated = 0, validated_by = :emp_id, validated_at = NOW() 
                WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':emp_id' => $employeeId, ':id' => $reviewId]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['success'] = 'Avis rejete.';
        } else {
            $_SESSION['error'] = 'Avis introuvable.';
        }
    }

} catch (PDOException $e) {
    error_log('Erreur traitement avis: ' . $e->getMessage());
    $_SESSION['error'] = 'Erreur lors du traitement de l\'avis.';
}

header('Location: dashboard.php');
exit;
