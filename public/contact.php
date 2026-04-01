<?php
require_once __DIR__ . '/../src/config/session.php';

// Traitement du formulaire de contact
$messageSent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    $errors = [];
    if (empty($name)) $errors[] = 'Le nom est obligatoire.';
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Veuillez entrer un email valide.';
    if (empty($subject)) $errors[] = 'Le sujet est obligatoire.';
    if (empty($message)) $errors[] = 'Le message est obligatoire.';

    if (empty($errors)) {
        // En production on enverrait un email ici
        $messageSent = true;
    } else {
        $_SESSION['error'] = implode(' ', $errors);
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact - EcoRide</title>
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
                        <li class="nav-item"><a class="nav-link active" href="contact.php"><i class="bi bi-envelope"></i> Contact</a></li>
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
                                    <i class="bi bi-envelope-paper"></i>
                                </div>
                                <h1 class="legal-title">Contactez-nous</h1>
                                <p class="text-muted">Une question, une suggestion ou un probleme ? Ecrivez-nous !</p>
                            </div>

                            <?php if ($messageSent): ?>
                                <div class="alert alert-success text-center">
                                    <i class="bi bi-check-circle me-2"></i>Votre message a ete envoye avec succes ! Nous vous repondrons dans les plus brefs delais.
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                                </div>
                            <?php endif; ?>

                            <?php if (!$messageSent): ?>
                            <form method="POST" action="contact.php" novalidate>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label"><i class="bi bi-person me-1"></i>Votre nom</label>
                                        <input type="text" class="form-control" id="name" name="name" required placeholder="Jean Dupont"
                                               value="<?php echo isLoggedIn() ? htmlspecialchars($_SESSION['user_pseudo'] ?? '') : ''; ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <label for="email" class="form-label"><i class="bi bi-envelope me-1"></i>Votre email</label>
                                        <input type="email" class="form-control" id="email" name="email" required placeholder="jean@exemple.com">
                                    </div>
                                    <div class="col-12">
                                        <label for="subject" class="form-label"><i class="bi bi-chat-text me-1"></i>Sujet</label>
                                        <select class="form-select" id="subject" name="subject" required>
                                            <option value="">-- Choisir un sujet --</option>
                                            <option value="question">Question generale</option>
                                            <option value="bug">Signaler un probleme</option>
                                            <option value="suggestion">Suggestion d'amelioration</option>
                                            <option value="partenariat">Proposition de partenariat</option>
                                            <option value="autre">Autre</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <label for="message" class="form-label"><i class="bi bi-pencil me-1"></i>Votre message</label>
                                        <textarea class="form-control" id="message" name="message" rows="6" required placeholder="Ecrivez votre message ici..."></textarea>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-eco-primary w-100">
                                            <i class="bi bi-send me-2"></i>Envoyer le message
                                        </button>
                                    </div>
                                </div>
                            </form>
                            <?php endif; ?>

                            <div class="row g-4 mt-5">
                                <div class="col-md-4 text-center">
                                    <div class="contact-info-card">
                                        <i class="bi bi-geo-alt contact-info-icon"></i>
                                        <h6>Adresse</h6>
                                        <p class="text-muted small mb-0">12 Rue de l'Ecologie<br>75001 Paris, France</p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="contact-info-card">
                                        <i class="bi bi-telephone contact-info-icon"></i>
                                        <h6>Telephone</h6>
                                        <p class="text-muted small mb-0">+33 1 23 45 67 89<br>Lun-Ven : 9h-18h</p>
                                    </div>
                                </div>
                                <div class="col-md-4 text-center">
                                    <div class="contact-info-card">
                                        <i class="bi bi-envelope-at contact-info-icon"></i>
                                        <h6>Email</h6>
                                        <p class="text-muted small mb-0">contact@ecoride.fr<br>support@ecoride.fr</p>
                                    </div>
                                </div>
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
