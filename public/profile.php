<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Vehicle.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter pour acceder a votre profil.';
    header('Location: login.php');
    exit;
}

$userModel = new User();
$vehicleModel = new Vehicle();
$userId = getUserId();
$user = $userModel->getUserById($userId);
$vehicles = $vehicleModel->getUserVehicles($userId);

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .profile-container { padding: 3rem 0; margin-top: 100px; }
        .profile-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 2rem; margin-bottom: 1.5rem; }
        .profile-header { display: flex; align-items: center; gap: 1.5rem; margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #e0e0e0; }
        .profile-avatar { width: 80px; height: 80px; background: var(--eco-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2rem; color: white; font-weight: 700; }
        .profile-info h1 { font-family: var(--font-heading); color: var(--eco-primary); font-size: 1.75rem; margin-bottom: .25rem; }
        .profile-info p { color: var(--eco-gray); margin-bottom: 0; }
        .credits-badge { background: linear-gradient(135deg, var(--eco-primary), var(--eco-secondary)); color: white; padding: .5rem 1rem; border-radius: 20px; font-weight: 600; font-size: 1rem; }
        .section-title { font-family: var(--font-heading); color: var(--eco-text); font-size: 1.25rem; font-weight: 600; margin-bottom: 1.5rem; }
        .form-label { font-weight: 600; color: var(--eco-text); margin-bottom: .5rem; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: .75rem; }
        .form-control:focus, .form-select:focus { border-color: var(--eco-primary); box-shadow: 0 0 0 .2rem rgba(46,125,50,.25); }
        .preference-option { display: flex; align-items: center; padding: 1rem; background: #f8f9fa; border-radius: 8px; margin-bottom: .75rem; }
        .preference-option label { margin-left: .75rem; margin-bottom: 0; cursor: pointer; }
        .vehicle-card { background: #f8f9fa; border-radius: 8px; padding: 1.25rem; margin-bottom: 1rem; }
        .vehicle-card-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: .75rem; }
        .vehicle-name { font-weight: 600; color: var(--eco-text); font-size: 1.1rem; }
        .vehicle-eco-badge { background: var(--eco-accent); color: var(--eco-text); padding: .25rem .75rem; border-radius: 12px; font-size: .8rem; font-weight: 600; }
        .vehicle-details { color: var(--eco-gray); font-size: .9rem; }
        .vehicle-details span { margin-right: 1rem; }
        .vehicle-actions { margin-top: 1rem; display: flex; gap: .5rem; }
        .no-vehicles { text-align: center; padding: 2rem; color: var(--eco-gray); }
        .role-badge { display: inline-block; padding: .25rem .75rem; border-radius: 12px; font-size: .85rem; font-weight: 600; text-transform: capitalize; }
        .role-badge.driver { background: #e3f2fd; color: #1565c0; }
        .role-badge.passenger { background: #fff3e0; color: #ef6c00; }
        .role-badge.both { background: #e8f5e9; color: #2e7d32; }
        .alert { border-radius: 8px; border: none; }
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
                        <li class="nav-item"><a class="nav-link" href="index.php">Accueil</a></li>
                        <li class="nav-item"><a class="nav-link" href="carpools.php">Covoiturages</a></li>
                        <li class="nav-item"><a class="nav-link active" href="profile.php">Mon Profil</a></li>
                        <?php if (getUserRole() === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Administration</a></li>
                        <?php endif; ?>
                        <?php if (in_array(getUserRole(), ['employee', 'admin'])): ?>
                            <li class="nav-item"><a class="nav-link" href="employee/dashboard.php">Espace Employe</a></li>
                        <?php endif; ?>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="profile-container">
            <div class="container">
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

                <div class="row">
                    <div class="col-lg-4">
                        <div class="profile-card">
                            <div class="profile-header flex-column text-center">
                                <div class="profile-avatar mx-auto">
                                    <?php echo strtoupper(substr($user['pseudo'], 0, 1)); ?>
                                </div>
                                <div class="profile-info text-center mt-3">
                                    <h1><?php echo htmlspecialchars($user['pseudo']); ?></h1>
                                    <p><?php echo htmlspecialchars($user['email']); ?></p>
                                    <?php if ($user['role_selection']): ?>
                                        <span class="role-badge <?php echo $user['role_selection']; ?> mt-2">
                                            <?php
                                            $roleLabels = ['driver' => 'Conducteur', 'passenger' => 'Passager', 'both' => 'Les deux'];
                                            echo $roleLabels[$user['role_selection']] ?? ucfirst($user['role_selection']);
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="text-center">
                                <p class="mb-2">Vos credits</p>
                                <span class="credits-badge"><?php echo number_format($user['credits'], 2); ?> &euro;</span>
                            </div>

                            <div class="mt-4">
                                <p class="mb-2"><strong>Preferences :</strong></p>
                                <ul class="list-unstyled mb-0">
                                    <li>🚬 Fumeur : <?php echo $user['smoking_allowed'] ? 'Autorise' : 'Non autorise'; ?></li>
                                    <li>🐾 Animaux : <?php echo $user['pets_allowed'] ? 'Autorises' : 'Non autorises'; ?></li>
                                </ul>
                            </div>

                            <div class="mt-4 text-muted small">
                                Membre depuis <?php echo date('F Y', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="profile-card">
                            <h2 class="section-title">Modifier le profil</h2>
                            <form action="profile_update.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="update_profile">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="pseudo" class="form-label">Nom d'utilisateur</label>
                                        <input type="text" class="form-control" id="pseudo" name="pseudo" value="<?php echo htmlspecialchars($user['pseudo']); ?>" required minlength="3" maxlength="50">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">Adresse email</label>
                                        <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="role_selection" class="form-label">Je souhaite etre</label>
                                    <select class="form-select" id="role_selection" name="role_selection">
                                        <option value="">-- Choisir un role --</option>
                                        <option value="driver" <?php echo $user['role_selection'] === 'driver' ? 'selected' : ''; ?>>Conducteur (proposer des trajets)</option>
                                        <option value="passenger" <?php echo $user['role_selection'] === 'passenger' ? 'selected' : ''; ?>>Passager (reserver des trajets)</option>
                                        <option value="both" <?php echo $user['role_selection'] === 'both' ? 'selected' : ''; ?>>Les deux (conducteur et passager)</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Preferences</label>
                                    <div class="preference-option">
                                        <input type="checkbox" class="form-check-input" id="smoking_allowed" name="smoking_allowed" value="1" <?php echo $user['smoking_allowed'] ? 'checked' : ''; ?>>
                                        <label for="smoking_allowed">🚬 Autoriser le tabac en voiture</label>
                                    </div>
                                    <div class="preference-option">
                                        <input type="checkbox" class="form-check-input" id="pets_allowed" name="pets_allowed" value="1" <?php echo $user['pets_allowed'] ? 'checked' : ''; ?>>
                                        <label for="pets_allowed">🐾 Autoriser les animaux en voiture</label>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-eco-primary">Enregistrer</button>
                            </form>
                        </div>

                        <div class="profile-card">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h2 class="section-title mb-0">Mes vehicules</h2>
                                <?php if ($user['role_selection'] === 'driver' || $user['role_selection'] === 'both'): ?>
                                    <a href="vehicle_add.php" class="btn btn-eco-primary btn-sm">+ Ajouter</a>
                                <?php endif; ?>
                            </div>

                            <?php if (empty($vehicles)): ?>
                                <div class="no-vehicles">
                                    <p class="mb-2">Vous n'avez pas encore ajoute de vehicule.</p>
                                    <?php if ($user['role_selection'] === 'driver' || $user['role_selection'] === 'both'): ?>
                                        <a href="vehicle_add.php" class="btn btn-eco-outline btn-sm">Ajouter votre premier vehicule</a>
                                    <?php else: ?>
                                        <p class="small">Selectionnez le role « Conducteur » ou « Les deux » pour ajouter des vehicules.</p>
                                    <?php endif; ?>
                                </div>
                            <?php else: ?>
                                <?php foreach ($vehicles as $vehicle): ?>
                                    <div class="vehicle-card">
                                        <div class="vehicle-card-header">
                                            <div class="vehicle-name"><?php echo htmlspecialchars($vehicle['brand'] . ' ' . $vehicle['model']); ?></div>
                                            <?php if ($vehicleModel->isEcoVehicle($vehicle['energy_type'])): ?>
                                                <span class="vehicle-eco-badge">🌱 Eco</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vehicle-details">
                                            <span>⚡ <?php echo ucfirst($vehicle['energy_type']); ?></span>
                                            <span>👥 <?php echo $vehicle['seats']; ?> places</span>
                                            <?php if ($vehicle['color']): ?>
                                                <span>🎨 <?php echo htmlspecialchars($vehicle['color']); ?></span>
                                            <?php endif; ?>
                                            <?php if ($vehicle['license_plate']): ?>
                                                <span>🚗 <?php echo htmlspecialchars($vehicle['license_plate']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="vehicle-actions">
                                            <a href="vehicle_add.php?edit=<?php echo $vehicle['id']; ?>" class="btn btn-eco-outline btn-sm">Modifier</a>
                                            <form action="vehicle_process.php" method="POST" style="display: inline;" onsubmit="return confirm('Supprimer ce vehicule ?');">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm">Supprimer</button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </div>

                        <div class="profile-card">
                            <h2 class="section-title">Changer le mot de passe</h2>
                            <form action="profile_update.php" method="POST">
                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                <input type="hidden" name="action" value="change_password">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mot de passe actuel</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" required minlength="8">
                                    <div class="form-text">Minimum 8 caracteres</div>
                                </div>
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Confirmer le nouveau mot de passe</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                <button type="submit" class="btn btn-eco-outline">Changer le mot de passe</button>
                            </form>
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
