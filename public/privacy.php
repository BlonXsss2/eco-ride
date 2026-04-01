<?php
require_once __DIR__ . '/../src/config/session.php';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialite - EcoRide</title>
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
                                    <i class="bi bi-shield-lock"></i>
                                </div>
                                <h1 class="legal-title">Politique de confidentialite</h1>
                                <p class="text-muted">Derniere mise a jour : <?php echo date('d/m/Y'); ?></p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-info-circle me-2"></i>Introduction</h3>
                                <p>
                                    La presente politique de confidentialite decrit comment EcoRide collecte, utilise et protege
                                    vos donnees personnelles lorsque vous utilisez notre plateforme de covoiturage ecologique.
                                    Nous nous engageons a proteger votre vie privee conformement au Reglement General sur la
                                    Protection des Donnees (RGPD).
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-database me-2"></i>Donnees collectees</h3>
                                <p>Nous collectons les donnees suivantes lors de votre utilisation de notre plateforme :</p>
                                <ul class="legal-list">
                                    <li><strong>Donnees d'identification :</strong> nom d'utilisateur (pseudo), adresse email</li>
                                    <li><strong>Donnees de connexion :</strong> mot de passe (chiffre et non lisible), adresse IP</li>
                                    <li><strong>Donnees de profil :</strong> preferences (fumeur, animaux), role choisi (conducteur/passager)</li>
                                    <li><strong>Donnees de vehicule :</strong> marque, modele, type d'energie, immatriculation (conducteurs uniquement)</li>
                                    <li><strong>Donnees de trajet :</strong> lieux de depart et d'arrivee, horaires, nombre de places</li>
                                    <li><strong>Donnees de transaction :</strong> historique de reservations, solde de credits</li>
                                </ul>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-bullseye me-2"></i>Finalites du traitement</h3>
                                <p>Vos donnees sont utilisees pour :</p>
                                <ul class="legal-list">
                                    <li>Creer et gerer votre compte utilisateur</li>
                                    <li>Mettre en relation conducteurs et passagers</li>
                                    <li>Gerer le systeme de credits et les reservations</li>
                                    <li>Permettre les avis et evaluations entre utilisateurs</li>
                                    <li>Assurer la securite et la moderation de la plateforme</li>
                                    <li>Ameliorer nos services et l'experience utilisateur</li>
                                </ul>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-clock-history me-2"></i>Duree de conservation</h3>
                                <p>
                                    Vos donnees personnelles sont conservees pendant toute la duree de votre inscription sur la plateforme,
                                    puis supprimees dans un delai de 3 ans apres la derniere activite de votre compte.
                                    Les donnees de facturation sont conservees pendant 10 ans conformement aux obligations legales.
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-people me-2"></i>Partage des donnees</h3>
                                <p>
                                    Vos donnees ne sont jamais vendues a des tiers. Seules les informations necessaires au bon
                                    fonctionnement du covoiturage sont partagees entre les utilisateurs concernes
                                    (pseudo, preferences de trajet). Nous pouvons etre amenes a communiquer vos donnees
                                    aux autorites competentes sur demande legale.
                                </p>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-lock me-2"></i>Securite des donnees</h3>
                                <p>
                                    Nous mettons en oeuvre des mesures techniques et organisationnelles appropriees pour proteger
                                    vos donnees :
                                </p>
                                <ul class="legal-list">
                                    <li>Chiffrement des mots de passe (bcrypt)</li>
                                    <li>Protection CSRF sur tous les formulaires</li>
                                    <li>Connexion securisee (HTTPS en production)</li>
                                    <li>Acces restreint aux donnees par role (utilisateur, employe, administrateur)</li>
                                </ul>
                            </div>

                            <div class="legal-section">
                                <h3><i class="bi bi-person-check me-2"></i>Vos droits</h3>
                                <p>Conformement au RGPD, vous disposez des droits suivants :</p>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-6">
                                        <div class="rights-card">
                                            <i class="bi bi-eye"></i>
                                            <strong>Droit d'acces</strong>
                                            <p class="small mb-0">Consulter vos donnees personnelles</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="rights-card">
                                            <i class="bi bi-pencil-square"></i>
                                            <strong>Droit de rectification</strong>
                                            <p class="small mb-0">Corriger vos donnees inexactes</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="rights-card">
                                            <i class="bi bi-trash3"></i>
                                            <strong>Droit a l'effacement</strong>
                                            <p class="small mb-0">Demander la suppression de vos donnees</p>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="rights-card">
                                            <i class="bi bi-download"></i>
                                            <strong>Droit a la portabilite</strong>
                                            <p class="small mb-0">Recevoir vos donnees dans un format lisible</p>
                                        </div>
                                    </div>
                                </div>
                                <p class="mt-3">
                                    Pour exercer vos droits, contactez-nous a <a href="mailto:contact@ecoride.fr" class="text-eco-link">contact@ecoride.fr</a>
                                    ou via notre <a href="contact.php" class="text-eco-link">formulaire de contact</a>.
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
