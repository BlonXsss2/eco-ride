<?php
require_once __DIR__ . '/../src/config/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoRide - Covoiturage ecologique</title>

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
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="index.php"><i class="bi bi-house"></i> Accueil</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="carpools.php"><i class="bi bi-car-front"></i> Covoiturages</a>
                        </li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item">
                                <a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Mon Profil</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bi bi-person-circle"></i> <?php echo htmlspecialchars($_SESSION['user_pseudo']); ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                                    <li><span class="dropdown-item-text text-muted small"><i class="bi bi-wallet2 me-1"></i>Credits : <?php echo number_format($_SESSION['user_credits'], 2); ?> &euro;</span></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person me-2"></i>Mon Profil</a></li>
                                    <?php if (getUserRole() === 'admin'): ?>
                                        <li><a class="dropdown-item" href="admin/dashboard.php"><i class="bi bi-gear me-2"></i>Administration</a></li>
                                    <?php endif; ?>
                                    <?php if (in_array(getUserRole(), ['employee', 'admin'])): ?>
                                        <li><a class="dropdown-item" href="employee/dashboard.php"><i class="bi bi-briefcase me-2"></i>Espace Employe</a></li>
                                    <?php endif; ?>
                                    <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right me-2"></i>Deconnexion</a></li>
                                </ul>
                            </li>
                        <?php else: ?>
                            <li class="nav-item">
                                <a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Inscription</a>
                            </li>
                        <?php endif; ?>
                        <li class="nav-item">
                            <a class="nav-link" href="contact.php"><i class="bi bi-envelope"></i> Contact</a>
                        </li>
                    </ul>

                    <?php if (isLoggedIn()): ?>
                        <div class="d-none d-lg-flex ms-3">
                            <a href="carpools.php" class="btn btn-sm btn-outline-light rounded-pill"><i class="bi bi-plus-circle me-1"></i>Proposer un trajet</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="container mt-3 mb-0">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="container mt-3">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            </div>
        <?php endif; ?>

        <section class="hero-section">
            <div class="container">
                <div class="hero-banner">
                    <div class="row gy-4 align-items-center">
                        <div class="col-lg-6">
                            <h1 class="hero-title">Covoiturage ecologique pour un avenir plus vert.</h1>
                            <p class="hero-subtitle mb-4">
                                Partagez vos trajets, reduisez les emissions de CO<sub>2</sub> et economisez avec EcoRide.
                                Trouvez des trajets eco-optimises en quelques clics.
                            </p>
                            <div class="d-flex gap-3 flex-wrap">
                                <a href="#popular" class="btn btn-outline-light rounded-pill px-4"><i class="bi bi-arrow-down-circle me-2"></i>Decouvrir</a>
                                <?php if (!isLoggedIn()): ?>
                                    <a href="register.php" class="btn btn-light rounded-pill px-4 text-success fw-semibold"><i class="bi bi-person-plus me-2"></i>S'inscrire</a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="search-card">
                                <h5 class="mb-3 text-center" style="color: var(--eco-primary); font-family: var(--font-heading); font-weight: 600;">
                                    <i class="bi bi-search me-2"></i>Trouver un trajet
                                </h5>
                                <form action="carpool_search.php" method="get" class="row g-3">
                                    <div class="col-12 col-md-4">
                                        <label for="from_city" class="form-label"><i class="bi bi-geo-alt me-1"></i>Depart</label>
                                        <input type="text" id="from_city" name="from_city" class="form-control" placeholder="ex. Paris">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="to_city" class="form-label"><i class="bi bi-geo me-1"></i>Arrivee</label>
                                        <input type="text" id="to_city" name="to_city" class="form-control" placeholder="ex. Lyon">
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label for="date" class="form-label"><i class="bi bi-calendar3 me-1"></i>Date</label>
                                        <input type="date" id="date" name="date" class="form-control">
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-eco-primary w-100 mt-1">
                                            <i class="bi bi-search me-2"></i>Rechercher un trajet
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="features-section">
            <div class="container">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-tree"></i>
                            </div>
                            <h5>100% Ecologique</h5>
                            <p>Privilegiez les vehicules electriques et hybrides pour un impact minimal sur l'environnement.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <h5>Trajet Securise</h5>
                            <p>Systeme d'avis et de notes pour des trajets en toute confiance entre utilisateurs verifies.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <i class="bi bi-piggy-bank"></i>
                            </div>
                            <h5>Economique</h5>
                            <p>Partagez les frais de trajet et economisez avec notre systeme de credits simple et transparent.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="carpool-section" id="popular">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2 class="h4 mb-1"><i class="bi bi-star me-2"></i>Covoiturages ecologiques populaires</h2>
                        <p class="text-muted mb-0 small">Decouvrez les trajets les plus recherches sur EcoRide</p>
                    </div>
                    <a href="carpools.php" class="btn btn-eco-outline btn-sm"><i class="bi bi-grid me-1"></i>Voir tout</a>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-4">
                        <article class="card carpool-card">
                            <div class="carpool-card-header">
                                <span class="fw-semibold"><i class="bi bi-person-circle me-1"></i>Alice · Electrique</span>
                                <span class="carpool-badge-eco"><i class="bi bi-lightning-charge me-1"></i>Eco</span>
                            </div>
                            <div class="carpool-card-body">
                                <div class="carpool-route"><i class="bi bi-arrow-right-circle me-1"></i>Paris → Lyon</div>
                                <div class="carpool-meta mb-2"><i class="bi bi-clock me-1"></i>Demain · 08:30 · 3 places disponibles</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">24 &euro;</span>
                                    <a href="carpool_search.php?from_city=Paris&to_city=Lyon" class="btn btn-sm btn-eco-primary"><i class="bi bi-search me-1"></i>Rechercher</a>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <article class="card carpool-card">
                            <div class="carpool-card-header">
                                <span class="fw-semibold"><i class="bi bi-person-circle me-1"></i>Lucas · Hybride</span>
                                <span class="carpool-badge-eco"><i class="bi bi-lightning-charge me-1"></i>Eco</span>
                            </div>
                            <div class="carpool-card-body">
                                <div class="carpool-route"><i class="bi bi-arrow-right-circle me-1"></i>Marseille → Nice</div>
                                <div class="carpool-meta mb-2"><i class="bi bi-clock me-1"></i>Sam. · 09:00 · 2 places disponibles</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">18 &euro;</span>
                                    <a href="carpool_search.php?from_city=Marseille&to_city=Nice" class="btn btn-sm btn-eco-primary"><i class="bi bi-search me-1"></i>Rechercher</a>
                                </div>
                            </div>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <article class="card carpool-card">
                            <div class="carpool-card-header">
                                <span class="fw-semibold"><i class="bi bi-person-circle me-1"></i>Emma · Electrique</span>
                                <span class="carpool-badge-eco"><i class="bi bi-lightning-charge me-1"></i>Eco</span>
                            </div>
                            <div class="carpool-card-body">
                                <div class="carpool-route"><i class="bi bi-arrow-right-circle me-1"></i>Toulouse → Bordeaux</div>
                                <div class="carpool-meta mb-2"><i class="bi bi-clock me-1"></i>Dim. · 17:15 · 1 place disponible</div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="price-tag">22 &euro;</span>
                                    <a href="carpool_search.php?from_city=Toulouse&to_city=Bordeaux" class="btn btn-sm btn-eco-primary"><i class="bi bi-search me-1"></i>Rechercher</a>
                                </div>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="about-section">
            <div class="container">
                <div class="row align-items-center gy-4">
                    <div class="col-lg-6">
                        <div class="about-pill mb-3">
                            <i class="bi bi-tree me-1"></i> EcoRide · Notre mission
                        </div>
                        <h2 class="h3 mb-3">Rouler ensemble, respirer mieux.</h2>
                        <p class="mb-3">
                            EcoRide connecte conducteurs et passagers pour partager des trajets eco-responsables.
                            En privilegiant les vehicules electriques et hybrides et en optimisant le remplissage,
                            nous reduisons les emissions de CO<sub>2</sub> a chaque voyage.
                        </p>
                        <ul class="about-list ps-3 mb-4">
                            <li>Score ecologique pour chaque covoiturage selon le type d'energie et la distance.</li>
                            <li>Systeme de recompenses avec des credits verts pour les covoitureurs reguliers.</li>
                            <li>Tarification transparente et avis pour instaurer la confiance.</li>
                        </ul>
                        <a href="contact.php" class="btn btn-eco-outline"><i class="bi bi-arrow-right me-2"></i>En savoir plus</a>
                    </div>
                    <div class="col-lg-6">
                        <div class="about-image-frame">
                            <img src="images/car-on-highway-stockcake.jpg" alt="Voiture roulant sur une autoroute verte">
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <div class="row gy-4">
                <div class="col-lg-4">
                    <h5 class="footer-brand"><i class="bi bi-ev-front"></i> EcoRide</h5>
                    <p class="footer-text mt-2">Covoiturage ecologique pour un avenir plus vert. Partagez vos trajets et reduisez votre empreinte carbone.</p>
                    <div class="footer-socials mt-3">
                        <a href="#" class="footer-social-link"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-twitter-x"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="footer-social-link"><i class="bi bi-linkedin"></i></a>
                    </div>
                </div>
                <div class="col-lg-2 col-md-4">
                    <h6 class="footer-heading">Navigation</h6>
                    <ul class="footer-links">
                        <li><a href="index.php"><i class="bi bi-chevron-right me-1"></i>Accueil</a></li>
                        <li><a href="carpools.php"><i class="bi bi-chevron-right me-1"></i>Covoiturages</a></li>
                        <li><a href="contact.php"><i class="bi bi-chevron-right me-1"></i>Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Informations</h6>
                    <ul class="footer-links">
                        <li><a href="mentions.php"><i class="bi bi-chevron-right me-1"></i>Mentions legales</a></li>
                        <li><a href="privacy.php"><i class="bi bi-chevron-right me-1"></i>Politique de confidentialite</a></li>
                        <li><a href="contact.php"><i class="bi bi-chevron-right me-1"></i>Support</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Contact</h6>
                    <ul class="footer-links footer-contact">
                        <li><i class="bi bi-geo-alt me-2"></i>12 Rue de l'Ecologie, Paris</li>
                        <li><i class="bi bi-envelope me-2"></i>contact@ecoride.fr</li>
                        <li><i class="bi bi-telephone me-2"></i>+33 1 23 45 67 89</li>
                    </ul>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="row align-items-center">
                <div class="col-md-6 text-center text-md-start">
                    <small class="footer-copyright">&copy; <?php echo date('Y'); ?> EcoRide. Tous droits reserves.</small>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <small class="footer-copyright">Fait avec <i class="bi bi-heart-fill text-danger"></i> pour la planete</small>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
