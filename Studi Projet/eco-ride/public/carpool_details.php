<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/Carpool.php';

$carpoolId = filter_var($_GET['id'] ?? 0, FILTER_VALIDATE_INT);

if (!$carpoolId) {
    $_SESSION['error'] = 'ID de covoiturage invalide';
    header('Location: index.php');
    exit;
}

$carpoolModel = new Carpool();
$carpool = $carpoolModel->getCarpoolDetails($carpoolId);

if (!$carpool) {
    $_SESSION['error'] = 'Covoiturage introuvable';
    header('Location: index.php');
    exit;
}

$driverReviews = $carpoolModel->getDriverReviews((int)$carpool['driver_id']);
$departureDate = new DateTime($carpool['departure_datetime']);
$driverRating = round((float)$carpool['driver_rating'], 1);
$isEco = (bool)$carpool['is_eco'];

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$alreadyBooked = false;
if (isLoggedIn()) {
    require_once __DIR__ . '/../src/models/Booking.php';
    $bookingModel = new Booking();
    $existing = $bookingModel->checkExistingBooking($carpoolId, (int)$_SESSION['user_id']);
    $alreadyBooked = ($existing !== null);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details du covoiturage - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .detail-section { padding: 3rem 0; margin-top: 100px; }
        .detail-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.08); overflow: hidden; margin-bottom: 1.5rem; }
        .detail-card-header { background: linear-gradient(135deg, var(--eco-primary), var(--eco-secondary)); color: var(--eco-white); padding: 1.25rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .detail-card-body { padding: 1.5rem; }
        .info-label { font-weight: 600; color: var(--eco-text); min-width: 140px; display: inline-block; }
        .info-row { padding: .5rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-row:last-child { border-bottom: none; }
        .driver-avatar { width: 70px; height: 70px; background: var(--eco-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; color: white; font-weight: 700; }
        .preference-badge { display: inline-block; padding: .35rem .75rem; border-radius: 20px; font-size: .85rem; font-weight: 500; margin-right: .5rem; margin-bottom: .5rem; }
        .preference-yes { background: #e8f5e9; color: #2e7d32; }
        .preference-no { background: #ffebee; color: #c62828; }
        .review-card { background: #f8f9fa; border-radius: 8px; padding: 1rem 1.25rem; margin-bottom: .75rem; }
        .review-stars { color: #f9a825; }
        .review-meta { font-size: .85rem; color: var(--eco-gray); }
        .rating-big { font-size: 2.5rem; font-weight: 700; color: var(--eco-primary); line-height: 1; }
        .rating-stars-big { color: #f9a825; font-size: 1.25rem; }
        .booking-section { background: #f8f9fa; border-radius: 12px; padding: 1.5rem; text-align: center; }
    </style>
</head>
<body class="page-wrapper">
    <header>
        <nav class="navbar navbar-expand-lg navbar-eco">
            <div class="container">
                <a class="navbar-brand" href="index.php">EcoRide</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="carpools.php">Covoiturages</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item"><a class="nav-link" href="profile.php">Mon Profil</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown">
                                    <?php echo htmlspecialchars($_SESSION['user_pseudo']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li><span class="dropdown-item-text text-muted small">Credits : <?php echo number_format($_SESSION['user_credits'], 2); ?> &euro;</span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="profile.php">Mon Profil</a></li>
                                    <li><a class="dropdown-item" href="logout.php">Deconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php">Connexion</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.php">Inscription</a></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container mt-3 mb-0">
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <section class="detail-section">
            <div class="container">
                <a href="javascript:history.back()" class="btn btn-outline-secondary mb-4">← Retour</a>

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Informations trajet -->
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h3 class="h5 mb-0"><?php echo htmlspecialchars($carpool['from_city']); ?> → <?php echo htmlspecialchars($carpool['to_city']); ?></h3>
                                <?php if ($isEco): ?><span class="carpool-badge-eco">Eco ★</span><?php endif; ?>
                            </div>
                            <div class="detail-card-body">
                                <div class="info-row"><span class="info-label">Date</span> <?php echo $departureDate->format('d/m/Y'); ?></div>
                                <div class="info-row"><span class="info-label">Heure de depart</span> <?php echo $departureDate->format('H:i'); ?></div>
                                <div class="info-row"><span class="info-label">Prix par place</span> <span class="fw-bold text-success"><?php echo number_format((float)$carpool['price'], 2); ?> &euro;</span></div>
                                <div class="info-row"><span class="info-label">Places disponibles</span> <?php echo $carpool['seats_available']; ?> / <?php echo $carpool['total_seats']; ?></div>
                            </div>
                        </div>

                        <!-- Vehicule -->
                        <?php if ($carpool['brand'] && $carpool['model']): ?>
                        <div class="detail-card">
                            <div class="detail-card-header">
                                <h3 class="h5 mb-0">Vehicule</h3>
                                <?php if ($isEco): ?><span class="carpool-badge-eco"><?php echo ucfirst(htmlspecialchars($carpool['energy_type'])); ?></span><?php endif; ?>
                            </div>
                            <div class="detail-card-body">
                                <div class="info-row"><span class="info-label">Marque / Modele</span> <?php echo htmlspecialchars($carpool['brand']); ?> <?php echo htmlspecialchars($carpool['model']); ?></div>
                                <div class="info-row"><span class="info-label">Type d'energie</span> <?php echo ucfirst(htmlspecialchars($carpool['energy_type'])); ?></div>
                                <?php if ($carpool['color']): ?>
                                <div class="info-row"><span class="info-label">Couleur</span> <?php echo htmlspecialchars($carpool['color']); ?></div>
                                <?php endif; ?>
                                <?php if ($carpool['license_plate']): ?>
                                <div class="info-row"><span class="info-label">Immatriculation</span> <?php echo htmlspecialchars($carpool['license_plate']); ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Avis conducteur -->
                        <div class="detail-card">
                            <div class="detail-card-header"><h3 class="h5 mb-0">Avis sur le conducteur</h3></div>
                            <div class="detail-card-body">
                                <?php if (!empty($driverReviews)): ?>
                                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                                        <div class="rating-big"><?php echo $driverRating; ?></div>
                                        <div>
                                            <div class="rating-stars-big">
                                                <?php for ($i = 1; $i <= 5; $i++): echo $i <= round($driverRating) ? '★' : '☆'; endfor; ?>
                                            </div>
                                            <div class="text-muted small"><?php echo count($driverReviews); ?> avis valide<?php echo count($driverReviews) > 1 ? 's' : ''; ?></div>
                                        </div>
                                    </div>

                                    <?php foreach ($driverReviews as $review): ?>
                                        <div class="review-card">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <div>
                                                    <span class="review-stars"><?php for ($i = 1; $i <= 5; $i++) echo $i <= (int)$review['rating'] ? '★' : '☆'; ?></span>
                                                    <strong class="ms-2"><?php echo htmlspecialchars($review['reviewer_pseudo']); ?></strong>
                                                </div>
                                                <span class="review-meta"><?php echo date('d/m/Y', strtotime($review['created_at'])); ?></span>
                                            </div>
                                            <?php if ($review['comment']): ?>
                                                <p class="mb-1"><?php echo htmlspecialchars($review['comment']); ?></p>
                                            <?php endif; ?>
                                            <small class="text-muted">Trajet : <?php echo htmlspecialchars($review['from_city']); ?> → <?php echo htmlspecialchars($review['to_city']); ?></small>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <p class="text-muted text-center py-3 mb-0">Aucun avis valide pour ce conducteur.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <!-- Conducteur -->
                        <div class="detail-card">
                            <div class="detail-card-header"><h3 class="h5 mb-0">Conducteur</h3></div>
                            <div class="detail-card-body text-center">
                                <div class="driver-avatar mx-auto mb-3"><?php echo strtoupper(substr($carpool['driver_pseudo'], 0, 1)); ?></div>
                                <h4 class="h5 mb-1"><?php echo htmlspecialchars($carpool['driver_pseudo']); ?></h4>
                                <?php if ($driverRating > 0): ?>
                                    <div class="mb-2">
                                        <span class="review-stars"><?php for ($i = 1; $i <= 5; $i++) echo $i <= round($driverRating) ? '★' : '☆'; ?></span>
                                        <span class="text-muted small">(<?php echo $driverRating; ?>)</span>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted small mb-2">Pas encore de note</p>
                                <?php endif; ?>
                                <hr>
                                <h5 class="h6 mb-3">Preferences</h5>
                                <div>
                                    <?php if ($carpool['smoking_allowed']): ?>
                                        <span class="preference-badge preference-yes">✓ Fumeur autorise</span>
                                    <?php else: ?>
                                        <span class="preference-badge preference-no">✗ Non fumeur</span>
                                    <?php endif; ?>
                                    <?php if ($carpool['pets_allowed']): ?>
                                        <span class="preference-badge preference-yes">✓ Animaux acceptes</span>
                                    <?php else: ?>
                                        <span class="preference-badge preference-no">✗ Pas d'animaux</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Reservation -->
                        <div class="booking-section">
                            <div class="mb-3">
                                <span class="price-tag"><?php echo number_format((float)$carpool['price'], 2); ?> &euro;</span>
                                <span class="text-muted">/ place</span>
                            </div>

                            <?php if (!isLoggedIn()): ?>
                                <p class="text-muted mb-3">Connectez-vous pour reserver ce covoiturage.</p>
                                <a href="login.php" class="btn btn-eco-primary w-100">Se connecter pour reserver</a>
                            <?php elseif ((int)$_SESSION['user_id'] === (int)$carpool['driver_id']): ?>
                                <p class="text-muted mb-0">C'est votre propre covoiturage.</p>
                            <?php elseif ($alreadyBooked): ?>
                                <div class="alert alert-success mb-0">Vous avez deja reserve ce covoiturage.</div>
                            <?php elseif ((int)$carpool['seats_available'] <= 0): ?>
                                <div class="alert alert-danger mb-0">Plus de places disponibles.</div>
                            <?php elseif ((float)$_SESSION['user_credits'] < 1): ?>
                                <div class="alert alert-danger mb-0">Credits insuffisants. Il vous faut au moins 1 credit.</div>
                            <?php else: ?>
                                <p class="text-muted small mb-3">
                                    Vos credits : <strong><?php echo number_format($_SESSION['user_credits'], 2); ?> &euro;</strong><br>
                                    Cout : <strong>1 credit</strong>
                                </p>
                                <button type="button" class="btn btn-eco-primary w-100" data-bs-toggle="modal" data-bs-target="#confirmBookingModal">Participer</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Modal de confirmation -->
    <?php if (isLoggedIn() && !$alreadyBooked && (int)$carpool['seats_available'] > 0 && (float)$_SESSION['user_credits'] >= 1 && (int)$_SESSION['user_id'] !== (int)$carpool['driver_id']): ?>
    <div class="modal fade" id="confirmBookingModal" tabindex="-1" aria-labelledby="confirmBookingLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header" style="background: var(--eco-primary); color: white;">
                    <h5 class="modal-title" id="confirmBookingLabel">Confirmer la reservation</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Vous etes sur le point de reserver le covoiturage suivant :</p>
                    <ul class="list-unstyled">
                        <li><strong>Trajet :</strong> <?php echo htmlspecialchars($carpool['from_city']); ?> → <?php echo htmlspecialchars($carpool['to_city']); ?></li>
                        <li><strong>Date :</strong> <?php echo $departureDate->format('d/m/Y'); ?> a <?php echo $departureDate->format('H:i'); ?></li>
                        <li><strong>Conducteur :</strong> <?php echo htmlspecialchars($carpool['driver_pseudo']); ?></li>
                        <li><strong>Prix :</strong> <?php echo number_format((float)$carpool['price'], 2); ?> &euro;</li>
                    </ul>
                    <hr>
                    <p class="mb-0">
                        <strong>1 credit</strong> sera deduit de votre solde.<br>
                        Credits restants apres reservation : <strong><?php echo number_format($_SESSION['user_credits'] - 1, 2); ?> &euro;</strong>
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annuler</button>
                    <form action="book_carpool.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="carpool_id" value="<?php echo $carpool['id']; ?>">
                        <button type="submit" class="btn btn-eco-primary">Oui, confirmer</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

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
                        <li><a href="index.php">Accueil</a></li>
                        <li><a href="carpools.php">Covoiturages</a></li>
                        <li><a href="contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Informations</h6>
                    <ul class="footer-links">
                        <li><a href="mentions.php">Mentions legales</a></li>
                        <li><a href="privacy.php">Politique de confidentialite</a></li>
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
