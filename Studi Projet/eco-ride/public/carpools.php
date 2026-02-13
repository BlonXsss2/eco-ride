<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/Carpool.php';

$results = $_SESSION['search_results'] ?? [];
$searchParams = $_SESSION['search_params'] ?? null;

unset($_SESSION['search_results']);
unset($_SESSION['search_params']);

$hasSearch = ($searchParams !== null);

$from = $hasSearch ? htmlspecialchars($searchParams['from']) : '';
$to = $hasSearch ? htmlspecialchars($searchParams['to']) : '';
$date = $hasSearch ? htmlspecialchars($searchParams['date']) : '';
$filters = $hasSearch ? ($searchParams['filters'] ?? []) : [];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultats de recherche - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body class="page-wrapper">
    <header>
        <nav class="navbar navbar-expand-lg navbar-eco">
            <div class="container">
                <a class="navbar-brand" href="index.php"><i class="bi bi-ev-front"></i> EcoRide</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link" href="index.php"><i class="bi bi-house"></i> Accueil</a></li>
                        <li class="nav-item"><a class="nav-link active" href="carpools.php"><i class="bi bi-car-front"></i> Covoiturages</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Mon Profil</a></li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_pseudo']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><span class="dropdown-item-text text-muted small"><i class="bi bi-wallet2 me-1"></i>Credits : <?php echo number_format($_SESSION['user_credits'], 2); ?> &euro;</span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Deconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
                            <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Inscription</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-envelope"></i> Contact</a></li>
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

        <section class="carpool-section py-5" style="margin-top: 100px;">
            <div class="container">

                <?php if (!$hasSearch): ?>
                    <!-- Pas de recherche en cours, on affiche le formulaire de recherche -->
                    <div class="row justify-content-center mb-5">
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-body p-4">
                                    <h2 class="h4 mb-4 text-center">Rechercher un covoiturage</h2>
                                    <form action="carpool_search.php" method="get" class="row g-3">
                                        <div class="col-12 col-md-4">
                                            <label for="from_city" class="form-label">Depart</label>
                                            <input type="text" id="from_city" name="from_city" class="form-control" placeholder="ex. Paris" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label for="to_city" class="form-label">Arrivee</label>
                                            <input type="text" id="to_city" name="to_city" class="form-control" placeholder="ex. Lyon" required>
                                        </div>
                                        <div class="col-12 col-md-4">
                                            <label for="date" class="form-label">Date</label>
                                            <input type="date" id="date" name="date" class="form-control">
                                        </div>
                                        <div class="col-12 text-center">
                                            <button type="submit" class="btn btn-eco-primary mt-2">Rechercher</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                <?php else: ?>
                    <!-- Resultats de recherche -->
                    <div class="row">
                        <div class="col-lg-3 mb-4">
                            <div class="card">
                                <div class="card-header bg-eco-primary text-white">
                                    <h5 class="mb-0">Filtres</h5>
                                </div>
                                <div class="card-body">
                                    <form method="get" action="carpool_search.php">
                                        <input type="hidden" name="from_city" value="<?php echo htmlspecialchars($from); ?>">
                                        <input type="hidden" name="to_city" value="<?php echo htmlspecialchars($to); ?>">
                                        <?php if ($date): ?>
                                            <input type="hidden" name="date" value="<?php echo htmlspecialchars($date); ?>">
                                        <?php endif; ?>

                                        <div class="mb-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="eco_only" value="1" id="eco_only" <?php echo (!empty($filters['eco_only'])) ? 'checked' : ''; ?>>
                                                <label class="form-check-label" for="eco_only">Ecologique uniquement</label>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label for="max_price" class="form-label">Prix max (&euro;)</label>
                                            <input type="number" class="form-control" id="max_price" name="max_price" value="<?php echo htmlspecialchars($filters['max_price'] ?? ''); ?>" min="0" step="0.01" placeholder="Sans limite">
                                        </div>

                                        <div class="mb-3">
                                            <label for="min_rating" class="form-label">Note min. conducteur</label>
                                            <select class="form-select" id="min_rating" name="min_rating">
                                                <option value="">Toutes les notes</option>
                                                <option value="3" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 3) ? 'selected' : ''; ?>>3+ etoiles</option>
                                                <option value="4" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 4) ? 'selected' : ''; ?>>4+ etoiles</option>
                                                <option value="5" <?php echo (isset($filters['min_rating']) && $filters['min_rating'] == 5) ? 'selected' : ''; ?>>5 etoiles</option>
                                            </select>
                                        </div>

                                        <button type="submit" class="btn btn-eco-primary w-100">Appliquer</button>
                                        <a href="carpool_search.php?from_city=<?php echo urlencode($from); ?>&to_city=<?php echo urlencode($to); ?><?php echo $date ? '&date=' . urlencode($date) : ''; ?>" class="btn btn-outline-secondary w-100 mt-2">Reinitialiser</a>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-9">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div>
                                    <h2 class="h4 mb-1">Resultats de recherche</h2>
                                    <p class="text-muted mb-0">
                                        <?php echo htmlspecialchars($from); ?> → <?php echo htmlspecialchars($to); ?>
                                        <?php if ($date): ?>
                                            · <?php echo date('d/m/Y', strtotime($date)); ?>
                                        <?php endif; ?>
                                    </p>
                                </div>
                                <a href="carpools.php" class="btn btn-eco-outline btn-sm">Nouvelle recherche</a>
                            </div>

                            <?php if (empty($results)): ?>
                                <div class="card">
                                    <div class="card-body text-center py-5">
                                        <h3 class="h5 mb-3">Aucun covoiturage trouve</h3>
                                        <p class="text-muted mb-4">
                                            Aucun covoiturage disponible ne correspond a vos criteres de recherche.
                                        </p>
                                        <?php
                                        $carpoolModel = new Carpool();
                                        $nearest = $carpoolModel->findNearestCarpools($from, $to, 3);
                                        if (!empty($nearest)):
                                        ?>
                                            <div class="mt-4">
                                                <p class="mb-3"><strong>Suggestions :</strong></p>
                                                <div class="row g-3">
                                                    <?php foreach ($nearest as $near): ?>
                                                        <div class="col-md-4">
                                                            <a href="carpool_details.php?id=<?php echo $near['id']; ?>" class="text-decoration-none">
                                                                <div class="card h-100">
                                                                    <div class="card-body">
                                                                        <small class="text-muted">Date alternative</small>
                                                                        <p class="mb-1 text-dark"><?php echo htmlspecialchars($near['from_city']); ?> → <?php echo htmlspecialchars($near['to_city']); ?></p>
                                                                        <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($near['departure_datetime'])); ?></small>
                                                                    </div>
                                                                </div>
                                                            </a>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <a href="carpools.php" class="btn btn-eco-primary mt-4">Nouvelle recherche</a>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="mb-3">
                                    <p class="text-muted">
                                        <strong><?php echo count($results); ?></strong> covoiturage<?php echo count($results) > 1 ? 's' : ''; ?> trouve<?php echo count($results) > 1 ? 's' : ''; ?>
                                    </p>
                                </div>

                                <div class="row g-4">
                                    <?php foreach ($results as $carpool): ?>
                                        <?php
                                        $departureDate = new DateTime($carpool['departure_datetime']);
                                        $driverRating = round((float)$carpool['driver_rating'], 1);
                                        $isEco = (bool)$carpool['is_eco'];
                                        ?>
                                        <div class="col-md-6 col-lg-4">
                                            <article class="card carpool-card">
                                                <div class="carpool-card-header">
                                                    <span class="fw-semibold">
                                                        <?php echo htmlspecialchars($carpool['driver_pseudo']); ?>
                                                        <?php if ($carpool['energy_type']): ?>
                                                            · <?php echo ucfirst(htmlspecialchars($carpool['energy_type'])); ?>
                                                        <?php endif; ?>
                                                        <?php if ($driverRating > 0): ?>
                                                            · ⭐ <?php echo $driverRating; ?>
                                                        <?php endif; ?>
                                                    </span>
                                                    <?php if ($isEco): ?>
                                                        <span class="carpool-badge-eco">Eco ★</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="carpool-card-body">
                                                    <div class="carpool-route">
                                                        <?php echo htmlspecialchars($carpool['from_city']); ?> → <?php echo htmlspecialchars($carpool['to_city']); ?>
                                                    </div>
                                                    <div class="carpool-meta mb-2">
                                                        <?php echo $departureDate->format('d/m'); ?> · <?php echo $departureDate->format('H:i'); ?> · 
                                                        <?php echo $carpool['seats_available']; ?> place<?php echo $carpool['seats_available'] > 1 ? 's' : ''; ?> disponible<?php echo $carpool['seats_available'] > 1 ? 's' : ''; ?>
                                                    </div>
                                                    <?php if ($carpool['brand'] && $carpool['model']): ?>
                                                        <div class="small text-muted mb-2">
                                                            <?php echo htmlspecialchars($carpool['brand']); ?> <?php echo htmlspecialchars($carpool['model']); ?>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <span class="price-tag"><?php echo number_format((float)$carpool['price'], 2); ?> &euro; / place</span>
                                                        <a href="carpool_details.php?id=<?php echo $carpool['id']; ?>" class="btn btn-sm btn-eco-primary">Details</a>
                                                    </div>
                                                </div>
                                            </article>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </section>
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
