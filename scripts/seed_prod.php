<?php
require_once __DIR__ . '/../src/config/database.php';

// petit script pour seed la BDD sur Fly
// (utile si la base est vide et qu'on veut tester vite)

function logLine($msg) {
    echo $msg . PHP_EOL;
}

function envVal($key) {
    $v = getenv($key);
    if ($v === false || trim($v) === '') return null;
    return $v;
}

function loadInserts($path) {
    if (!file_exists($path)) return [];
    $lines = file($path, FILE_IGNORE_NEW_LINES);
    if (!$lines) return [];

    $sqls = [];
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '') continue;
        if (substr($line, 0, 2) === '--') continue;
        $sqls[] = $line; // 1 INSERT par ligne
    }
    return $sqls;
}

$pdo = null;

try {
    // juste pour etre sur qu'on a la config
    $host = envVal('DB_HOST');
    $port = envVal('DB_PORT');
    $name = envVal('DB_NAME');
    $user = envVal('DB_USER');
    $pass = envVal('DB_PASS');

    logLine('DB_HOST=' . ($host ?: '(non defini)'));
    logLine('DB_PORT=' . ($port ?: '(non defini)'));
    logLine('DB_NAME=' . ($name ?: '(non defini)'));
    logLine('DB_USER=' . ($user ?: '(non defini)'));

    $pdo = Database::getConnection();
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $before = (int)$pdo->query('SELECT COUNT(*) FROM carpools')->fetchColumn();
    logLine('carpools_count_before=' . $before);

    // hash pour "password"
    $pwd = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';

    $inserted = 0;

    $pdo->beginTransaction();
    $pdo->exec('SET FOREIGN_KEY_CHECKS=0');

    // users minimum pour les FK
    $pdo->exec(
        "INSERT INTO users (id, email, password, pseudo, role, role_selection, smoking_allowed, pets_allowed, credits, suspended)
         VALUES
         (1,'admin@ecoride.com',    '{$pwd}','Admin','admin',NULL,0,0,100.00,0),
         (2,'emp@ecoride.com',      '{$pwd}','Employee','employee',NULL,0,0,50.00,0),
         (3,'alice@example.com',    '{$pwd}','Alice','user','driver',0,1,20.00,0),
         (4,'lucas@example.com',    '{$pwd}','Lucas','user','both',0,0,20.00,0),
         (5,'emma@example.com',     '{$pwd}','Emma','user','passenger',1,1,20.00,0),
         (6,'marc@example.com',     '{$pwd}','Marc','user','driver',0,0,35.00,0),
         (7,'sophie@example.com',   '{$pwd}','Sophie','user','passenger',0,1,15.00,0),
         (8,'julien@example.com',   '{$pwd}','Julien','user','both',1,0,45.00,0),
         (9,'marie@example.com',    '{$pwd}','Marie','user','driver',0,1,25.00,0),
         (10,'thomas@example.com',  '{$pwd}','Thomas','user','passenger',0,0,20.00,0),
         (11,'emp2@ecoride.com',    '{$pwd}','Employee2','employee',NULL,0,0,50.00,0)
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

    // vehicles minimum pour les FK
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

    // import du gros fichier seulement si table vide
    if ($before === 0) {
        $sqls = loadInserts(__DIR__ . '/../database/carpools_inserts.sql');
        logLine('carpool_inserts_lines=' . count($sqls));
        foreach ($sqls as $sql) {
            $pdo->exec($sql);
            $inserted++;
        }
    }

    // on ajoute quelques trajets FUTURS "classiques" si y'en a pas
    $routes = [
        ['from' => 'Paris',     'to' => 'Lyon',      'price' => 24.00, 'seats' => 4, 'free' => 3, 'eco' => 1, 'driver' => 3, 'vehicle' => 1],
        ['from' => 'Marseille', 'to' => 'Nice',      'price' => 18.00, 'seats' => 4, 'free' => 2, 'eco' => 1, 'driver' => 4, 'vehicle' => 2],
    ];

    $check = $pdo->prepare(
        "SELECT COUNT(*) FROM carpools
         WHERE from_city = :from
           AND to_city = :to
           AND seats_available > 0
           AND departure_datetime >= NOW()"
    );
    $add = $pdo->prepare(
        "INSERT INTO carpools (driver_id, vehicle_id, from_city, to_city, departure_datetime, price, total_seats, seats_available, is_eco)
         VALUES (:driver_id, :vehicle_id, :from, :to, :dt, :price, :seats, :free, :eco)"
    );

    foreach ($routes as $r) {
        $check->execute([':from' => $r['from'], ':to' => $r['to']]);
        $ok = (int)$check->fetchColumn();
        if ($ok > 0) continue;

        for ($i = 1; $i <= 5; $i++) {
            // date simple (UTC), +1/+2... jours
            $dt = gmdate('Y-m-d H:i:s', time() + ($i * 86400) + (3600 * (8 + $i)));

            $add->execute([
                ':driver_id' => $r['driver'],
                ':vehicle_id' => $r['vehicle'],
                ':from' => $r['from'],
                ':to' => $r['to'],
                ':dt' => $dt,
                ':price' => $r['price'],
                ':seats' => $r['seats'],
                ':free' => $r['free'],
                ':eco' => $r['eco'],
            ]);
            $inserted++;
        }
    }

    $pdo->exec('SET FOREIGN_KEY_CHECKS=1');
    $pdo->commit();

    $after = (int)$pdo->query('SELECT COUNT(*) FROM carpools')->fetchColumn();
    logLine('inserted=' . $inserted);
    logLine('carpools_count_after=' . $after);
} catch (Exception $e) {
    if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
    logLine('ERROR: ' . $e->getMessage());
    exit(1);
}

