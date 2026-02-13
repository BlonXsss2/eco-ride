<?php
// Dashboard administrateur
require_once __DIR__ . '/../../src/config/session.php';
require_once __DIR__ . '/../../src/config/database.php';

// protection: admin seulement
if (!isLoggedIn() || getUserRole() !== 'admin') {
    $_SESSION['error'] = 'Acces refuse. Espace reserve aux administrateurs.';
    header('Location: ../login.php');
    exit;
}

if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$pdo = Database::getConnection();

// statistiques
$totalCreditsEarned = $pdo->query("SELECT COALESCE(SUM(seat_count), 0) FROM bookings WHERE status = 'accepted'")->fetchColumn();
$totalUsers   = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();
$totalCarpools = $pdo->query("SELECT COUNT(*) FROM carpools")->fetchColumn();

// donnees pour le graphique 1: covoiturages par jour (30 derniers jours)
$carpoolsPerDay = $pdo->query("
    SELECT DATE(created_at) AS day, COUNT(*) AS total
    FROM carpools
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(created_at)
    ORDER BY day ASC
")->fetchAll();

$cpLabels = [];
$cpData = [];
foreach ($carpoolsPerDay as $row) {
    $cpLabels[] = date('d/m', strtotime($row['day']));
    $cpData[] = (int)$row['total'];
}

// donnees pour le graphique 2: credits gagnes par jour
$creditsPerDay = $pdo->query("
    SELECT DATE(b.created_at) AS day, COALESCE(SUM(b.seat_count), 0) AS total
    FROM bookings b
    WHERE b.status = 'accepted'
      AND b.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(b.created_at)
    ORDER BY day ASC
")->fetchAll();

$crLabels = [];
$crData = [];
foreach ($creditsPerDay as $row) {
    $crLabels[] = date('d/m', strtotime($row['day']));
    $crData[] = (int)$row['total'];
}

// liste de tous les utilisateurs
$allUsers = $pdo->query("
    SELECT id, pseudo, email, role, suspended, created_at
    FROM users
    ORDER BY FIELD(role,'admin','employee','user','visitor'), pseudo ASC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administration - EcoRide</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <!-- Chart.js pour les graphiques -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-container { padding: 2rem 0; margin-top: 100px; }
        .dash-card { background: var(--eco-white); border-radius: 12px; box-shadow: 0 4px 12px rgba(0,0,0,.08); padding: 2rem; margin-bottom: 1.5rem; }
        .dash-title { font-family: var(--font-heading); color: var(--eco-primary); font-size: 1.75rem; font-weight: 700; margin-bottom: .5rem; }
        .section-title { font-family: var(--font-heading); color: var(--eco-text); font-size: 1.25rem; font-weight: 600; margin-bottom: 1rem; }
        .stat-card { text-align: center; padding: 1.5rem; }
        .stat-number { font-size: 2.5rem; font-weight: 700; color: var(--eco-primary); }
        .stat-label { color: var(--eco-gray); font-size: .9rem; }
        .table th { font-size: .85rem; text-transform: uppercase; color: var(--eco-gray); border-bottom: 2px solid var(--eco-primary); }
        .table td { vertical-align: middle; }
        .badge-admin { background: #e8eaf6; color: #283593; }
        .badge-employee { background: #e0f7fa; color: #00695c; }
        .badge-user { background: #e8f5e9; color: #2e7d32; }
        .badge-suspended { background: #ffebee; color: #c62828; }
        .badge-active { background: #e8f5e9; color: #2e7d32; }
        .chart-container { position: relative; height: 300px; }
        .form-label { font-weight: 600; color: var(--eco-text); }
        .form-control, .form-select { border: 2px solid #e0e0e0; border-radius: 8px; padding: .75rem; }
        .form-control:focus, .form-select:focus { border-color: var(--eco-primary); box-shadow: 0 0 0 .2rem rgba(46,125,50,.25); }
    </style>
</head>
<body class="page-wrapper">
    <header>
        <nav class="navbar navbar-expand-lg navbar-eco">
            <div class="container">
                <a class="navbar-brand" href="../index.php">EcoRide</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="mainNavbar">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item"><a class="nav-link active" href="dashboard.php">Administration</a></li>
                        <li class="nav-item"><a class="nav-link" href="../employee/dashboard.php">Espace Employe</a></li>
                        <li class="nav-item"><a class="nav-link" href="../index.php">Accueil</a></li>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><?php echo htmlspecialchars($_SESSION['user_pseudo']); ?> (Admin)</a>
                        </li>
                        <li class="nav-item"><a class="nav-link" href="../logout.php">Deconnexion</a></li>
                    </ul>
                </div>
            </div>
        </nav>
    </header>

    <main class="site-main">
        <div class="dashboard-container">
            <div class="container">
                <h1 class="dash-title mb-4">Tableau de bord Administrateur</h1>

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

                <!-- Statistiques -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <div class="stat-number"><?php echo $totalCreditsEarned; ?></div>
                            <div class="stat-label">Credits gagnes (total)</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <div class="stat-number"><?php echo $totalCarpools; ?></div>
                            <div class="stat-label">Covoiturages crees</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="dash-card stat-card">
                            <div class="stat-number"><?php echo $totalUsers; ?></div>
                            <div class="stat-label">Utilisateurs inscrits</div>
                        </div>
                    </div>
                </div>

                <!-- Graphiques -->
                <div class="row mb-4">
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <h2 class="section-title">Covoiturages par jour (30 jours)</h2>
                            <div class="chart-container">
                                <canvas id="carpoolsChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="dash-card">
                            <h2 class="section-title">Credits gagnes par jour (30 jours)</h2>
                            <div class="chart-container">
                                <canvas id="creditsChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Creer un employe -->
                <div class="dash-card">
                    <h2 class="section-title">Creer un compte employe</h2>
                    <form action="admin_process.php" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="action" value="create_employee">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label for="emp_pseudo" class="form-label">Pseudo</label>
                                <input type="text" class="form-control" id="emp_pseudo" name="pseudo" required minlength="3" maxlength="50">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="emp_email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="emp_email" name="email" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="emp_password" class="form-label">Mot de passe</label>
                                <input type="password" class="form-control" id="emp_password" name="password" required minlength="8">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-eco-primary">Creer l'employe</button>
                    </form>
                </div>

                <!-- Gestion des utilisateurs -->
                <div class="dash-card">
                    <h2 class="section-title">Gestion des utilisateurs</h2>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Pseudo</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Statut</th>
                                    <th>Inscrit le</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allUsers as $u): ?>
                                    <tr>
                                        <td>#<?php echo $u['id']; ?></td>
                                        <td><strong><?php echo htmlspecialchars($u['pseudo']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($u['email']); ?></td>
                                        <td>
                                            <?php
                                            // badge selon le role
                                            if ($u['role'] === 'admin') {
                                                $roleClass = 'badge-admin';
                                            } elseif ($u['role'] === 'employee') {
                                                $roleClass = 'badge-employee';
                                            } else {
                                                $roleClass = 'badge-user';
                                            }
                                            ?>
                                            <span class="badge <?php echo $roleClass; ?> rounded-pill px-3 py-1">
                                                <?php echo ucfirst($u['role']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ((int)$u['suspended'] === 1): ?>
                                                <span class="badge badge-suspended rounded-pill px-3 py-1">Suspendu</span>
                                            <?php else: ?>
                                                <span class="badge badge-active rounded-pill px-3 py-1">Actif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($u['created_at'])); ?></td>
                                        <td>
                                            <?php if ((int)$u['id'] !== getUserId()): ?>
                                                <?php if ((int)$u['suspended'] === 0): ?>
                                                    <form action="admin_process.php" method="POST" class="d-inline" onsubmit="return confirm('Suspendre ce compte ?');">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="suspend">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">Suspendre</button>
                                                    </form>
                                                <?php else: ?>
                                                    <form action="admin_process.php" method="POST" class="d-inline">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="action" value="reactivate">
                                                        <input type="hidden" name="user_id" value="<?php echo $u['id']; ?>">
                                                        <button type="submit" class="btn btn-sm btn-success">Reactiver</button>
                                                    </form>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="text-muted small">—</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
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
                        <li><a href="../index.php">Accueil</a></li>
                        <li><a href="../carpools.php">Covoiturages</a></li>
                        <li><a href="../contact.php">Contact</a></li>
                    </ul>
                </div>
                <div class="col-lg-3 col-md-4">
                    <h6 class="footer-heading">Informations</h6>
                    <ul class="footer-links">
                        <li><a href="../mentions.php">Mentions legales</a></li>
                        <li><a href="../privacy.php">Politique de confidentialite</a></li>
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
        // graphique covoiturages par jour
        var cpCtx = document.getElementById('carpoolsChart').getContext('2d');
        new Chart(cpCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($cpLabels); ?>,
                datasets: [{
                    label: 'Covoiturages',
                    data: <?php echo json_encode($cpData); ?>,
                    borderColor: '#2E7D32',
                    backgroundColor: 'rgba(46,125,50,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#2E7D32',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });

        // graphique credits par jour
        var crCtx = document.getElementById('creditsChart').getContext('2d');
        new Chart(crCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($crLabels); ?>,
                datasets: [{
                    label: 'Credits',
                    data: <?php echo json_encode($crData); ?>,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76,175,80,0.1)',
                    fill: true,
                    tension: 0.3,
                    pointBackgroundColor: '#4CAF50',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                },
                plugins: { legend: { display: false } }
            }
        });
    </script>
</body>
</html>
