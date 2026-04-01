<?php
require_once __DIR__ . '/../src/config/session.php';
require_once __DIR__ . '/../src/models/User.php';
require_once __DIR__ . '/../src/models/Vehicle.php';

if (!isLoggedIn()) {
    $_SESSION['error'] = 'Veuillez vous connecter pour gerer vos vehicules.';
    header('Location: login.php');
    exit;
}

$userModel = new User();
$vehicleModel = new Vehicle();
$userId = getUserId();
$user = $userModel->getUserById($userId);

if ($user['role_selection'] !== 'driver' && $user['role_selection'] !== 'both') {
    $_SESSION['error'] = 'Vous devez etre conducteur pour ajouter un vehicule. Mettez a jour votre profil.';
    header('Location: profile.php');
    exit;
}

$editMode = false;
$vehicle = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $vehicle = $vehicleModel->getVehicleById((int)$_GET['edit']);
    if ($vehicle && $vehicle['user_id'] == $userId) {
        $editMode = true;
    } else {
        $_SESSION['error'] = 'Vehicule introuvable.';
        header('Location: profile.php');
        exit;
    }
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
    <title><?php echo $editMode ? 'Modifier' : 'Ajouter'; ?> un vehicule - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .vehicle-container { min-height: calc(100vh - 200px); display: flex; align-items: center; padding: 3rem 0; margin-top: 80px; }
        .vehicle-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.1); padding: 2.5rem; max-width: 600px; margin: 0 auto; }
        .vehicle-title { font-family: var(--font-heading); color: var(--eco-primary); font-size: 1.75rem; font-weight: 700; margin-bottom: .5rem; text-align: center; }
        .vehicle-subtitle { color: var(--eco-gray); text-align: center; margin-bottom: 2rem; }
        .form-label { font-weight: 600; color: var(--eco-text); margin-bottom: .5rem; }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: .75rem; }
        .form-control:focus, .form-select:focus { border-color: var(--eco-primary); box-shadow: 0 0 0 .2rem rgba(46,125,50,.25); }
        .eco-info { background: #e8f5e9; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: .9rem; }
        .eco-info strong { color: var(--eco-primary); }
        .alert { border-radius: 8px; border: none; }
        .back-link { color: var(--eco-gray); text-decoration: none; display: inline-flex; align-items: center; margin-bottom: 1rem; }
        .back-link:hover { color: var(--eco-primary); }
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
                        <li class="nav-item"><a class="nav-link" href="profile.php">Mon Profil</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="vehicle-container">
            <div class="container">
                <div class="vehicle-card">
                    <a href="profile.php" class="back-link">← Retour au profil</a>
                    
                    <h1 class="vehicle-title"><?php echo $editMode ? 'Modifier' : 'Ajouter'; ?> un vehicule</h1>
                    <p class="vehicle-subtitle">
                        <?php echo $editMode ? 'Mettez a jour les informations de votre vehicule' : 'Ajoutez un vehicule pour proposer des covoiturages'; ?>
                    </p>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="eco-info">
                        🌱 <strong>Astuce ecologique :</strong> Les vehicules electriques et hybrides sont marques comme ecologiques,
                        aidant les passagers a trouver des trajets plus verts !
                    </div>

                    <form action="vehicle_process.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="<?php echo $editMode ? 'update' : 'add'; ?>">
                        <?php if ($editMode): ?>
                            <input type="hidden" name="vehicle_id" value="<?php echo $vehicle['id']; ?>">
                        <?php endif; ?>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="brand" class="form-label">Marque *</label>
                                <input type="text" class="form-control" id="brand" name="brand" value="<?php echo $editMode ? htmlspecialchars($vehicle['brand']) : ''; ?>" required maxlength="100" placeholder="ex. Tesla, Toyota, Renault">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="model" class="form-label">Modele *</label>
                                <input type="text" class="form-control" id="model" name="model" value="<?php echo $editMode ? htmlspecialchars($vehicle['model']) : ''; ?>" required maxlength="100" placeholder="ex. Model 3, Prius, Clio">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="energy_type" class="form-label">Type d'energie *</label>
                                <select class="form-select" id="energy_type" name="energy_type" required>
                                    <option value="">-- Choisir --</option>
                                    <option value="electric" <?php echo ($editMode && $vehicle['energy_type'] === 'electric') ? 'selected' : ''; ?>>⚡ Electrique (Eco)</option>
                                    <option value="hybrid" <?php echo ($editMode && $vehicle['energy_type'] === 'hybrid') ? 'selected' : ''; ?>>🔋 Hybride (Eco)</option>
                                    <option value="petrol" <?php echo ($editMode && $vehicle['energy_type'] === 'petrol') ? 'selected' : ''; ?>>⛽ Essence</option>
                                    <option value="diesel" <?php echo ($editMode && $vehicle['energy_type'] === 'diesel') ? 'selected' : ''; ?>>⛽ Diesel</option>
                                    <option value="other" <?php echo ($editMode && $vehicle['energy_type'] === 'other') ? 'selected' : ''; ?>>Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="seats" class="form-label">Places passagers *</label>
                                <input type="number" class="form-control" id="seats" name="seats" value="<?php echo $editMode ? $vehicle['seats'] : ''; ?>" required min="1" max="8" placeholder="Nombre de places (hors conducteur)">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="license_plate" class="form-label">Immatriculation</label>
                                <input type="text" class="form-control" id="license_plate" name="license_plate" value="<?php echo $editMode ? htmlspecialchars($vehicle['license_plate'] ?? '') : ''; ?>" maxlength="20" placeholder="ex. AB-123-CD">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="registration_date" class="form-label">Date de mise en circulation</label>
                                <input type="date" class="form-control" id="registration_date" name="registration_date" value="<?php echo $editMode ? ($vehicle['registration_date'] ?? '') : ''; ?>">
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="color" class="form-label">Couleur</label>
                            <input type="text" class="form-control" id="color" name="color" value="<?php echo $editMode ? htmlspecialchars($vehicle['color'] ?? '') : ''; ?>" maxlength="50" placeholder="ex. Blanc, Argent, Bleu">
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-eco-primary">
                                <?php echo $editMode ? 'Mettre a jour' : 'Ajouter le vehicule'; ?>
                            </button>
                            <a href="profile.php" class="btn btn-eco-outline">Annuler</a>
                        </div>
                    </form>
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
