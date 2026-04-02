<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/config/database.php';

function logLine(string $msg): void
{
    echo $msg . PHP_EOL;
}

function envOrFail(string $key): string
{
    $v = getenv($key);
    if ($v === false || trim($v) === '') {
        throw new RuntimeException("Missing env var: {$key}");
    }
    return (string)$v;
}

function loadCarpoolInserts(string $path): array
{
    if (!is_file($path)) {
        throw new RuntimeException("File not found: {$path}");
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        throw new RuntimeException("Unable to read: {$path}");
    }

    $stmts = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '--')) {
            continue;
        }
        // generated file is 1 INSERT per line, ending with ;
        $stmts[] = $line;
    }
    return $stmts;
}

try {
    // Ensure env exists (also acts as quick debug output)
    $host = envOrFail('DB_HOST');
    $port = envOrFail('DB_PORT');
    $name = envOrFail('DB_NAME');
    $user = envOrFail('DB_USER');
    envOrFail('DB_PASS');

    logLine("DB_HOST={$host}");
    logLine("DB_PORT={$port}");
    logLine("DB_NAME={$name}");
    logLine("DB_USER={$user}");

    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $carpoolsCount = (int)$pdo->query('SELECT COUNT(*) FROM carpools')->fetchColumn();
    logLine("carpools_count_before={$carpoolsCount}");

    if ($carpoolsCount > 0) {
        logLine('Carpools already present; skipping import.');
        exit(0);
    }

    // Make the minimum required referenced rows exist.
    // We insert explicit IDs to match the generated carpools file references:
    // - driver_id: up to 9
    // - vehicle_id: up to 8
    //
    // Use ON DUPLICATE KEY UPDATE to be idempotent.
    $passwordHash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // "password"

    $pdo->beginTransaction();
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    $pdo->exec(
        "INSERT INTO users (id, email, password, pseudo, role, role_selection, smoking_allowed, pets_allowed, credits, suspended)
         VALUES
         (1,'admin@ecoride.com',    '{$passwordHash}','Admin','admin',NULL,0,0,100.00,0),
         (2,'emp@ecoride.com',      '{$passwordHash}','Employee','employee',NULL,0,0,50.00,0),
         (3,'alice@example.com',    '{$passwordHash}','Alice','user','driver',0,1,20.00,0),
         (4,'lucas@example.com',    '{$passwordHash}','Lucas','user','both',0,0,20.00,0),
         (5,'emma@example.com',     '{$passwordHash}','Emma','user','passenger',1,1,20.00,0),
         (6,'marc@example.com',     '{$passwordHash}','Marc','user','driver',0,0,35.00,0),
         (7,'sophie@example.com',   '{$passwordHash}','Sophie','user','passenger',0,1,15.00,0),
         (8,'julien@example.com',   '{$passwordHash}','Julien','user','both',1,0,45.00,0),
         (9,'marie@example.com',    '{$passwordHash}','Marie','user','driver',0,1,25.00,0),
         (10,'thomas@example.com',  '{$passwordHash}','Thomas','user','passenger',0,0,20.00,0),
         (11,'emp2@ecoride.com',    '{$passwordHash}','Employee2','employee',NULL,0,0,50.00,0)
         ON DUPLICATE KEY UPDATE
           password=VALUES(password),
           pseudo=VALUES(pseudo),
           role=VALUES(role),
           role_selection=VALUES(role_selection),
           smoking_allowed=VALUES(smoking_allowed),
           pets_allowed=VALUES(pets_allowed),
           credits=VALUES(credits),
           suspended=VALUES(suspended)"
    );

    $pdo->exec(
        "INSERT INTO vehicles (id, user_id, brand, model, energy_type, seats, color, license_plate, registration_date)
         VALUES
         (1, 3,'Tesla','Model 3','electric',4,'White','AB-123-CD','2022-03-15'),
         (2, 4,'Toyota','Prius','hybrid',4,'Silver','EF-456-GH','2020-07-22'),
         (3, 3,'Renault','Clio','petrol',3,'Blue','IJ-789-KL','2019-11-08'),
         (4, 6,'Nissan','Leaf','electric',4,'Black','MN-012-OP','2023-01-10'),
         (5, 8,'Peugeot','3008','hybrid',5,'Grey','QR-345-ST','2021-06-20'),
         (6, 9,'Renault','Zoe','electric',4,'Red','UV-678-WX','2022-09-05'),
         (7, 6,'Citroen','C3','diesel',4,'White','YZ-901-AB','2018-04-12'),
         (8, 8,'Volkswagen','Golf','petrol',4,'Blue','CD-234-EF','2020-11-30')
         ON DUPLICATE KEY UPDATE
           user_id=VALUES(user_id),
           brand=VALUES(brand),
           model=VALUES(model),
           energy_type=VALUES(energy_type),
           seats=VALUES(seats),
           color=VALUES(color),
           license_plate=VALUES(license_plate),
           registration_date=VALUES(registration_date)"
    );

    $insertStmts = loadCarpoolInserts(__DIR__ . '/../database/carpools_inserts.sql');
    logLine('carpool_inserts_lines=' . count($insertStmts));

    $ok = 0;
    foreach ($insertStmts as $sql) {
        $pdo->exec($sql);
        $ok++;
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    $pdo->commit();

    $carpoolsCountAfter = (int)$pdo->query('SELECT COUNT(*) FROM carpools')->fetchColumn();
    logLine("inserted={$ok}");
    logLine("carpools_count_after={$carpoolsCountAfter}");
} catch (Throwable $e) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    logLine('ERROR: ' . $e->getMessage());
    exit(1);
}

