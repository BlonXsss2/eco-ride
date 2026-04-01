<?php
require_once __DIR__ . '/../src/config/session.php';

// rediriger si deja connecte
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

// token CSRF
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - EcoRide</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <link rel="stylesheet" href="css/style.css">

    <style>
        .login-container {
            min-height: calc(100vh - 200px);
            display: flex;
            align-items: center;
            padding: 3rem 0;
            margin-top: 80px;
        }

        .login-card {
            background: var(--eco-white);
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
            padding: 2.5rem;
            max-width: 500px;
            margin: 0 auto;
        }

        .login-title {
            font-family: var(--font-heading);
            color: var(--eco-primary);
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .login-subtitle {
            color: var(--eco-gray);
            text-align: center;
            margin-bottom: 2rem;
        }

        .form-label {
            font-weight: 600;
            color: var(--eco-text);
            margin-bottom: 0.5rem;
        }

        .form-control {
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 0.75rem;
            transition: border-color 0.2s;
        }

        .form-control:focus {
            border-color: var(--eco-primary);
            box-shadow: 0 0 0 0.2rem rgba(46, 125, 50, 0.25);
        }

        .password-input-group { position: relative; }

        .password-toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--eco-gray);
            cursor: pointer;
            padding: 0.5rem;
        }

        .password-toggle:hover { color: var(--eco-primary); }

        .alert { border-radius: 8px; border: none; padding: 1rem; margin-bottom: 1.5rem; text-align: left; }
        .alert-danger { background-color: #ffebee; color: #c62828; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; }

        .register-link { text-align: center; margin-top: 1.5rem; color: var(--eco-gray); }
        .register-link a { color: var(--eco-primary); font-weight: 600; text-decoration: none; }
        .register-link a:hover { text-decoration: underline; }
    </style>
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
                        <li class="nav-item"><a class="nav-link active" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="register.php"><i class="bi bi-person-plus"></i> Inscription</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-envelope"></i> Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="login-container">
            <div class="container">
                <div class="login-card">
                    <h1 class="login-title">Connexion</h1>
                    <p class="login-subtitle">Bienvenue sur EcoRide</p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <form id="loginForm" action="login_process.php" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required autocomplete="email">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="password" name="password" required autocomplete="current-password">
                                <button type="button" class="password-toggle" onclick="togglePassword()">
                                    <span id="passwordToggleIcon">👁️</span>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1">
                            <label class="form-check-label" for="remember_me">Se souvenir de moi</label>
                        </div>

                        <button type="submit" class="btn btn-eco-primary w-100">Se connecter</button>
                    </form>

                    <div class="register-link">
                        Pas encore de compte ? <a href="register.php">S'inscrire ici</a>
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
                        <p class="footer-text mt-2">Covoiturage ecologique pour un avenir plus vert.</p>
                        <div class="footer-socials mt-3">
                            <a href="#" class="footer-social-link"><i class="bi bi-facebook"></i></a>
                            <a href="#" class="footer-social-link"><i class="bi bi-twitter-x"></i></a>
                            <a href="#" class="footer-social-link"><i class="bi bi-instagram"></i></a>
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
                        </ul>
                    </div>
                    <div class="col-lg-3 col-md-4">
                        <h6 class="footer-heading">Contact</h6>
                        <ul class="footer-links footer-contact">
                            <li><i class="bi bi-geo-alt me-2"></i>Paris, France</li>
                            <li><i class="bi bi-envelope me-2"></i>contact@ecoride.fr</li>
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
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('passwordToggleIcon');
            if (field.type === 'password') { field.type = 'text'; icon.textContent = '🙈'; }
            else { field.type = 'password'; icon.textContent = '👁️'; }
        }

        const form = document.getElementById('loginForm');
        const email = document.getElementById('email');
        const password = document.getElementById('password');

        function validateEmail() {
            const v = email.value.trim();
            if (!v.includes('@') || !v.includes('.')) { email.setCustomValidity('Veuillez entrer une adresse email valide'); return false; }
            email.setCustomValidity(''); return true;
        }

        function validatePassword() {
            if (password.value.length === 0) { password.setCustomValidity('Le mot de passe est obligatoire'); return false; }
            password.setCustomValidity(''); return true;
        }

        email.addEventListener('input', validateEmail);
        password.addEventListener('input', validatePassword);

        form.addEventListener('submit', function(e) {
            if (!validateEmail() || !validatePassword()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    </script>
</body>
</html>
