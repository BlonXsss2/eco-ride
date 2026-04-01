<?php
require_once __DIR__ . '/../src/config/session.php';

if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .register-container { min-height: calc(100vh - 200px); display: flex; align-items: center; padding: 3rem 0; margin-top: 80px; }
        .register-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.1); padding: 2.5rem; max-width: 500px; margin: 0 auto; }
        .register-title { font-family: var(--font-heading); color: var(--eco-primary); font-size: 2rem; font-weight: 700; margin-bottom: .5rem; text-align: center; }
        .register-subtitle { color: var(--eco-gray); text-align: center; margin-bottom: 2rem; }
        .form-label { font-weight: 600; color: var(--eco-text); margin-bottom: .5rem; }
        .form-control { border: 2px solid #e0e0e0; border-radius: 8px; padding: .75rem; transition: border-color .2s; }
        .form-control:focus { border-color: var(--eco-primary); box-shadow: 0 0 0 .2rem rgba(46,125,50,.25); }
        .password-input-group { position: relative; }
        .password-toggle { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--eco-gray); cursor: pointer; padding: .5rem; }
        .password-toggle:hover { color: var(--eco-primary); }
        .password-strength { margin-top: .5rem; height: 4px; background: #e0e0e0; border-radius: 2px; overflow: hidden; }
        .password-strength-bar { height: 100%; transition: width .3s, background-color .3s; width: 0%; }
        .password-strength-weak { background-color: var(--eco-danger); width: 33%; }
        .password-strength-medium { background-color: var(--eco-warning); width: 66%; }
        .password-strength-strong { background-color: var(--eco-primary); width: 100%; }
        .password-strength-text { font-size: .85rem; margin-top: .25rem; color: var(--eco-gray); }
        .alert { border-radius: 8px; border: none; padding: 1rem; margin-bottom: 1.5rem; }
        .alert-danger { background-color: #ffebee; color: #c62828; }
        .alert-success { background-color: #e8f5e9; color: #2e7d32; }
        .login-link { text-align: center; margin-top: 1.5rem; color: var(--eco-gray); }
        .login-link a { color: var(--eco-primary); font-weight: 600; text-decoration: none; }
        .login-link a:hover { text-decoration: underline; }
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
                        <li class="nav-item"><a class="nav-link active" href="register.php"><i class="bi bi-person-plus"></i> Inscription</a></li>
                        <li class="nav-item"><a class="nav-link" href="login.php"><i class="bi bi-box-arrow-in-right"></i> Connexion</a></li>
                        <li class="nav-item"><a class="nav-link" href="contact.php"><i class="bi bi-envelope"></i> Contact</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="register-container">
            <div class="container">
                <div class="register-card">
                    <h1 class="register-title">Creer un compte</h1>
                    <p class="register-subtitle">Rejoignez EcoRide et partagez des trajets ecologiques</p>

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

                    <form id="registerForm" action="register_process.php" method="POST" novalidate>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

                        <div class="mb-3">
                            <label for="pseudo" class="form-label">Nom d'utilisateur</label>
                            <input type="text" class="form-control" id="pseudo" name="pseudo" required minlength="3" maxlength="50" pattern="[a-zA-Z0-9_]+" title="3 a 50 caracteres, lettres, chiffres et _ uniquement">
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Adresse email</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">Mot de passe</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="password" name="password" required minlength="8">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <span id="passwordToggleIcon">👁️</span>
                                </button>
                            </div>
                            <div class="password-strength"><div class="password-strength-bar" id="passwordStrengthBar"></div></div>
                            <div class="password-strength-text" id="passwordStrengthText"></div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Confirmer le mot de passe</label>
                            <div class="password-input-group">
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <span id="confirmPasswordToggleIcon">👁️</span>
                                </button>
                            </div>
                            <div class="invalid-feedback"></div>
                        </div>

                        <button type="submit" class="btn btn-eco-primary w-100">Creer mon compte</button>
                    </form>

                    <div class="login-link">
                        Deja un compte ? <a href="login.php">Se connecter</a>
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
    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId === 'password' ? 'passwordToggleIcon' : 'confirmPasswordToggleIcon');
            if (field.type === 'password') { field.type = 'text'; icon.textContent = '🙈'; }
            else { field.type = 'password'; icon.textContent = '👁️'; }
        }

        function checkPasswordStrength(pw) {
            let s = 0;
            if (pw.length >= 8) s++;
            if (pw.length >= 12) s++;
            if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
            if (/[0-9]/.test(pw)) s++;
            if (/[^a-zA-Z0-9]/.test(pw)) s++;
            const bar = document.getElementById('passwordStrengthBar');
            const txt = document.getElementById('passwordStrengthText');
            if (s <= 2) { bar.className = 'password-strength-bar password-strength-weak'; txt.textContent = pw.length > 0 ? 'Mot de passe faible' : ''; }
            else if (s <= 3) { bar.className = 'password-strength-bar password-strength-medium'; txt.textContent = 'Mot de passe moyen'; }
            else { bar.className = 'password-strength-bar password-strength-strong'; txt.textContent = 'Mot de passe fort'; }
        }

        document.getElementById('password').addEventListener('input', function(e) { checkPasswordStrength(e.target.value); });

        const form = document.getElementById('registerForm');
        const pseudo = document.getElementById('pseudo');
        const email = document.getElementById('email');
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');

        function validatePseudo() {
            const v = pseudo.value.trim();
            if (v.length < 3 || v.length > 50) { pseudo.setCustomValidity('Le pseudo doit faire entre 3 et 50 caracteres'); return false; }
            if (!/^[a-zA-Z0-9_]+$/.test(v)) { pseudo.setCustomValidity('Lettres, chiffres et _ uniquement'); return false; }
            pseudo.setCustomValidity(''); return true;
        }
        function validateEmail() {
            const v = email.value.trim();
            if (!v.includes('@') || !v.includes('.')) { email.setCustomValidity('Veuillez entrer un email valide'); return false; }
            email.setCustomValidity(''); return true;
        }
        function validatePassword() {
            if (password.value.length < 8) { password.setCustomValidity('Le mot de passe doit faire au moins 8 caracteres'); return false; }
            password.setCustomValidity(''); return true;
        }
        function validateConfirmPassword() {
            if (confirmPassword.value !== password.value) { confirmPassword.setCustomValidity('Les mots de passe ne correspondent pas'); return false; }
            confirmPassword.setCustomValidity(''); return true;
        }

        pseudo.addEventListener('input', validatePseudo);
        email.addEventListener('input', validateEmail);
        password.addEventListener('input', function() { validatePassword(); if (confirmPassword.value) validateConfirmPassword(); });
        confirmPassword.addEventListener('input', validateConfirmPassword);

        form.addEventListener('submit', function(e) {
            if (!validatePseudo() || !validateEmail() || !validatePassword() || !validateConfirmPassword()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    </script>
</body>
</html>
