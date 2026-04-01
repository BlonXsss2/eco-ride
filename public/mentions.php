<?php
require_once __DIR__ . '/../src/config/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mentions legales - EcoRide</title>
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
                        <li class="nav-item"><a class="nav-link" href="carpools.php"><i class="bi bi-car-front"></i> Covoiturages</a></li>
                        <?php if (isLoggedIn()): ?>
                            <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Mon Profil</a></li>
                            <li class="nav-item"><a class="nav-link" href="logout.php"><i class="bi bi-box-arrow-right"></i> Deconnexion</a></li>
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
        <div class="legal-page">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-8">
                        <div class="legal-card">
                            <div class="text-center mb-4">
                                <div class="legal-icon-circle mb-3">
                                    <i class="bi bi-file-earmark-text"></i>
                                </div>
                                <h1 class="legal-title">Mentions legales</h1>
                                <p class="text-muted">Derniere mise a jour : <?php echo date('d/m/Y'); ?></p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-building me-2"></i>Editeur du site</h3>
                                <p>Le site EcoRide est edite par :</p>
                                <ul class="legal-list">
                                    <li><strong>Raison sociale :</strong> EcoRide SAS</li>
                                    <li><strong>Siege social :</strong> 12 Rue de l'Ecologie, 75001 Paris, France</li>
                                    <li><strong>SIRET :</strong> 123 456 789 00001</li>
                                    <li><strong>Capital social :</strong> 10 000 &euro;</li>
                                    <li><strong>Directeur de publication :</strong> Jean-Pierre Martin</li>
                                    <li><strong>Email :</strong> contact@ecoride.fr</li>
                                    <li><strong>Telephone :</strong> +33 1 23 45 67 89</li>
                                </ul>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-hdd-rack me-2"></i>Hebergement</h3>
                                <p>Le site est heberge par :</p>
                                <ul class="legal-list">
                                    <li><strong>Hebergeur :</strong> OVH SAS</li>
                                    <li><strong>Adresse :</strong> 2 Rue Kellermann, 59100 Roubaix, France</li>
                                    <li><strong>Site web :</strong> www.ovh.com</li>
                                </ul>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-c-circle me-2"></i>Propriete intellectuelle</h3>
                                <p>
                                    L'ensemble des contenus (textes, images, graphismes, logo, icones, etc.) presents sur le site EcoRide
                                    sont proteges par le droit d'auteur et le droit de la propriete intellectuelle. Toute reproduction,
                                    distribution ou modification sans autorisation prealable est strictement interdite.
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-exclamation-triangle me-2"></i>Limitation de responsabilite</h3>
                                <p>
                                    EcoRide met tout en oeuvre pour fournir des informations fiables et actualisees. Toutefois,
                                    le site ne saurait garantir l'exactitude, la completude ou l'actualite des informations diffusees.
                                    EcoRide se reserve le droit de modifier le contenu du site a tout moment et sans preavis.
                                </p>
                                <p>
                                    EcoRide agit en tant qu'intermediaire de mise en relation entre conducteurs et passagers.
                                    La responsabilite d'EcoRide ne saurait etre engagee en cas de litige entre utilisateurs
                                    dans le cadre d'un covoiturage.
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-cookie me-2"></i>Cookies</h3>
                                <p>
                                    Le site EcoRide utilise des cookies de session necessaires au bon fonctionnement de la plateforme
                                    (authentification, panier de credits). Aucun cookie publicitaire ou de pistage n'est utilise.
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-balance-scale me-2 d-none"></i><i class="bi bi-shield-check me-2"></i>Droit applicable</h3>
                                <p>
                                    Les presentes mentions legales sont regies par le droit francais. En cas de litige, les tribunaux
                                    francais seront seuls competents.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
                        <li><a href="contact.php">Support</a></li>
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
