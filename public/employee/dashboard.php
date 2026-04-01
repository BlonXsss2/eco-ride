<?php
// Dashboard employe - validation des avis
require_once __DIR__ . '/../../src/config/session.php';
require_once __DIR__ . '/../../src/config/database.php';

// protection: employe ou admin seulement
if (!isLoggedIn() || !in_array(getUserRole(), ['employee', 'admin'])) {
    $_SESSION['error'] = 'Acces refuse. Espace reserve aux employes.';
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = Database::getConnection();

// recuperer les avis en attente
$pendingReviewsSql = "SELECT r.id, r.rating, r.comment, r.created_at,
                             u_passenger.pseudo AS passenger_pseudo,
                             u_driver.pseudo AS driver_pseudo,
                             c.from_city, c.to_city, c.departure_datetime
                      FROM reviews r
                      INNER JOIN users u_passenger ON r.passenger_id = u_passenger.id
                      INNER JOIN carpools c ON r.carpool_id = c.id
                      INNER JOIN users u_driver ON c.driver_id = u_driver.id
                      WHERE r.validated = 0 AND r.rejected = 0
                      ORDER BY r.created_at DESC";
$pendingReviews = $pdo->query($pendingReviewsSql)->fetchAll();

// trajets problematiques (annules ou refuses)
$problemTripsSql = "SELECT c.id AS carpool_id,
                           c.from_city, c.to_city, c.departure_datetime, c.price,
                           u_driver.pseudo AS driver_pseudo, u_driver.email AS driver_email,
                           u_passenger.pseudo AS passenger_pseudo, u_passenger.email AS passenger_email,
                           b.status AS booking_status
                    FROM bookings b
                    INNER JOIN carpools c ON b.carpool_id = c.id
                    INNER JOIN users u_driver ON c.driver_id = u_driver.id
                    INNER JOIN users u_passenger ON b.passenger_id = u_passenger.id
                    WHERE b.status IN ('declined', 'cancelled')
                    ORDER BY c.departure_datetime DESC
                    LIMIT 50";
$problemTrips = $pdo->query($problemTripsSql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Espace Employe - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .dashboard-container { padding: 2rem 0; margin-top: 100px; }
        .dash-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 2rem; margin-bottom: 1.5rem; }
        .dash-title { font-family: var(--font-heading); color: var(--eco-primary); font-size: 1.75rem; font-weight: 700; margin-bottom: .5rem; }
        .section-title { font-family: var(--font-heading); color: var(--eco-text); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; }
        .badge-pending { background: #fff3e0; color: #ef6c00; }
        .badge-declined { background: #ffebee; color: #c62828; }
        .badge-cancelled { background: #fce4ec; color: #ad1457; }
        .star { color: #ffb300; }
        .table th { font-size: .85rem; text-transform: uppercase; color: var(--eco-gray); border-bottom: 2px solid var(--eco-primary); }
        .table td { vertical-align: middle; }
        .stat-card { text-align: center; padding: 1.5rem; }
        .stat-number { font-size: 2rem; font-weight: 700; color: var(--eco-primary); }
        .stat-label { color: var(--eco-gray); font-size: .9rem; }
    </style>
</head>
<body class="page-wrapper">
    <header>
        <nav class="navbar navbar-expand-lg navbar-eco">
            <div class="container">
                <a class="navbar-brand" href="../index.php">EcoRide</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Tableau de bord</a></li>
                        <li class="nav-item"><a class="nav-link" href="../index.php">Accueil</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><?php echo htmlspecialchars($_SESSION['user_pseudo']); ?> (Employe)</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="../logout.php">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="dashboard-container">
            <div class="container">
                <h1 class="dash-title mb-4">Tableau de bord Employe</h1>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistiques rapides -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <div class="stat-number"><?php echo count($pendingReviews); ?></div>
                            <div class="stat-label">Avis en attente</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <?php $totalValidated = $pdo->query("SELECT COUNT(*) FROM reviews WHERE validated = 1")->fetchColumn(); ?>
                            <div class="stat-number"><?php echo $totalValidated; ?></div>
                            <div class="stat-label">Avis valides</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <div class="stat-number"><?php echo count($problemTrips); ?></div>
                            <div class="stat-label">Trajets problematiques</div>
                        </div>
                    </div>
                </div>

                <!-- Avis en attente -->
                <div class="dash-card">
                    <h2 class="section-title">Avis en attente de validation</h2>

                    <?php if (empty($pendingReviews)): ?>
                        <div class="text-center py-4 text-muted">
                            <p>Aucun avis en attente de validation.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Conducteur</th>
                                        <th>Passager</th>
                                        <th>Trajet</th>
                                        <th>Note</th>
                                        <th>Commentaire</th>
                                        <th>Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingReviews as $review): ?>
                                        <tr>
                                            <td><strong><?php echo htmlspecialchars($review['driver_pseudo']); ?></strong></td>
                                            <td><?php echo htmlspecialchars($review['passenger_pseudo']); ?></td>
                                            <td><?php echo htmlspecialchars($review['from_city'] . ' → ' . $review['to_city']); ?></td>
                                            <td>
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star"><?php echo $i <= $review['rating'] ? '★' : '☆'; ?></span>
                                                <?php endfor; ?>
                                            </td>
                                            <td>
                                                <small><?php echo htmlspecialchars(substr($review['comment'] ?? '', 0, 80)); ?><?php echo strlen($review['comment'] ?? '') > 80 ? '...' : ''; ?></small>
                                            </td>
                                            <td><small><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></small></td>
                                            <td>
                                                <form action="review_process.php" method="POST" class="d-inline">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="approve">
                                                    <button type="submit" class="btn btn-sm btn-success">Approuver</button>
                                                </form>
                                                <form action="review_process.php" method="POST" class="d-inline" onsubmit="return confirm('Rejeter cet avis ?');">
                                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                    <input type="hidden" name="review_id" value="<?php echo $review['id']; ?>">
                                                    <input type="hidden" name="action" value="reject">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Rejeter</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Trajets problematiques -->
                <div class="dash-card">
                    <h2 class="section-title">Trajets problematiques</h2>

                    <?php if (empty($problemTrips)): ?>
                        <div class="text-center py-4 text-muted">
                            <p>Aucun trajet problematique.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Trajet</th>
                                        <th>Date</th>
                                        <th>Conducteur</th>
                                        <th>Passager</th>
                                        <th>Statut</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($problemTrips as $trip): ?>
                                        <tr>
                                            <td>#<?php echo $trip['carpool_id']; ?></td>
                                            <td><?php echo htmlspecialchars($trip['from_city'] . ' → ' . $trip['to_city']); ?></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($trip['departure_datetime'])); ?></td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($trip['driver_pseudo']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($trip['driver_email']); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($trip['passenger_pseudo']); ?></strong><br>
                                                <small class="text-muted"><?php echo htmlspecialchars($trip['passenger_email']); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($trip['booking_status'] === 'declined'): ?>
                                                    <span class="badge badge-declined rounded-pill px-3 py-1">Refuse</span>
                                                <?php else: ?>
                                                    <span class="badge badge-cancelled rounded-pill px-3 py-1">Annule</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <h5 class="footer-brand"><i class="bi bi-ev-front"></i> EcoRide</h5>
                    <p class="footer-text mt-2">Covoiturage ecologique pour un avenir plus vert.</p>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="footer-heading">Navigation</h6>
                    <ul class="footer-links">
                        <li><a href="../index.php">Accueil</a></li>
                        <li><a href="../carpools.php">Covoiturages</a></li>
                        <li><a href="../contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Informations</h6>
                    <ul class="footer-links">
                        <li><a href="../mentions.php">Mentions legales</a></li>
                        <li><a href="../privacy.php">Politique de confidentialite</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Contact</h6>
                    <ul class="footer-links footer-contact">
                        <li><i class="bi bi-envelope me-2"></i>contact@ecoride.fr</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="text-center">
                <small class="footer-copyright">&copy; <?php echo date('Y'); ?> EcoRide. Tous droits reserves.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
