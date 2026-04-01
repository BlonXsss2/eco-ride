CREATE DATABASE IF NOT EXISTS ecoride;
USE ecoride;

DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS bookings;
DROP TABLE IF EXISTS carpools;
DROP TABLE IF EXISTS vehicles;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    pseudo VARCHAR(50) NOT NULL UNIQUE,
    role ENUM('visitor','user','employee','admin') NOT NULL DEFAULT 'visitor',
    role_selection ENUM('driver','passenger','both') NULL DEFAULT NULL,
    smoking_allowed TINYINT(1) NOT NULL DEFAULT 0,
    pets_allowed TINYINT(1) NOT NULL DEFAULT 0,
    credits DECIMAL(8,2) NOT NULL DEFAULT 20.00,
    suspended TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_suspended (suspended)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vehicles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    brand VARCHAR(100) NOT NULL,
    model VARCHAR(100) NOT NULL,
    energy_type ENUM('electric','hybrid','petrol','diesel','other') NOT NULL,
    seats TINYINT UNSIGNED NOT NULL,
    color VARCHAR(50) DEFAULT NULL,
    license_plate VARCHAR(20) DEFAULT NULL UNIQUE,
    registration_date DATE NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_vehicles_user_id (user_id),
    INDEX idx_vehicles_energy_type (energy_type),
    CONSTRAINT fk_vehicles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE carpools (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    driver_id INT UNSIGNED NOT NULL,
    vehicle_id INT UNSIGNED DEFAULT NULL,
    from_city VARCHAR(120) NOT NULL,
    to_city VARCHAR(120) NOT NULL,
    departure_datetime DATETIME NOT NULL,
    price DECIMAL(8,2) NOT NULL,
    total_seats TINYINT UNSIGNED NOT NULL,
    seats_available TINYINT UNSIGNED NOT NULL,
    is_eco TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_carpools_driver_id (driver_id),
    INDEX idx_carpools_from_city (from_city),
    INDEX idx_carpools_to_city (to_city),
    INDEX idx_carpools_departure_datetime (departure_datetime),
    INDEX idx_carpools_is_eco (is_eco),
    INDEX idx_carpools_cities_date (from_city, to_city, departure_datetime),
    CONSTRAINT fk_carpools_driver
        FOREIGN KEY (driver_id) REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_carpools_vehicle
        FOREIGN KEY (vehicle_id) REFERENCES vehicles(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE bookings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carpool_id INT UNSIGNED NOT NULL,
    passenger_id INT UNSIGNED NOT NULL,
    seat_count TINYINT UNSIGNED NOT NULL DEFAULT 1,
    status ENUM('pending','accepted','declined','cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NULL DEFAULT NULL,
    INDEX idx_bookings_carpool_id (carpool_id),
    INDEX idx_bookings_passenger_id (passenger_id),
    INDEX idx_bookings_status (status),
    CONSTRAINT fk_bookings_carpool
        FOREIGN KEY (carpool_id) REFERENCES carpools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_bookings_passenger
        FOREIGN KEY (passenger_id) REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    UNIQUE KEY uk_bookings_carpool_passenger (carpool_id, passenger_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

CREATE TABLE reviews (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    carpool_id INT UNSIGNED NOT NULL,
    passenger_id INT UNSIGNED NOT NULL,
    rating TINYINT UNSIGNED NOT NULL,
    comment TEXT DEFAULT NULL,
    validated TINYINT(1) NOT NULL DEFAULT 0,
    rejected TINYINT(1) NOT NULL DEFAULT 0,
    validated_by INT UNSIGNED DEFAULT NULL,
    validated_at TIMESTAMP NULL DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_reviews_carpool_id (carpool_id),
    INDEX idx_reviews_passenger_id (passenger_id),
    INDEX idx_reviews_validated (validated),
    INDEX idx_reviews_rating (rating),
    CONSTRAINT fk_reviews_carpool
        FOREIGN KEY (carpool_id) REFERENCES carpools(id)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_passenger
        FOREIGN KEY (passenger_id) REFERENCES users(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    CONSTRAINT fk_reviews_employee
        FOREIGN KEY (validated_by) REFERENCES users(id)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    UNIQUE KEY uk_reviews_carpool_passenger (carpool_id, passenger_id)
) ENGINE=InnoDB
  DEFAULT CHARSET=utf8mb4
  COLLATE=utf8mb4_unicode_ci;

-- Donnees de test
-- mot de passe pour tous les comptes: password

INSERT INTO users (email, password, pseudo, role, role_selection, smoking_allowed, pets_allowed, credits, suspended) VALUES
('admin@ecoride.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin',    'admin',    NULL,        0, 0, 100.00, 0),
('emp@ecoride.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Employee', 'employee', NULL,        0, 0,  50.00, 0),
('alice@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice',    'user',     'driver',    0, 1,  20.00, 0),
('lucas@example.com',  '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lucas',    'user',     'both',      0, 0,  20.00, 0),
('emma@example.com',   '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma',     'user',     'passenger', 1, 1,  20.00, 0);

INSERT INTO vehicles (user_id, brand, model, energy_type, seats, color, license_plate, registration_date) VALUES
(3, 'Tesla',   'Model 3', 'electric', 4, 'White',  'AB-123-CD', '2022-03-15'),
(4, 'Toyota',  'Prius',   'hybrid',   4, 'Silver', 'EF-456-GH', '2020-07-22'),
(3, 'Renault', 'Clio',    'petrol',   3, 'Blue',   'IJ-789-KL', '2019-11-08');

INSERT INTO carpools (driver_id, vehicle_id, from_city, to_city, departure_datetime, price, total_seats, seats_available, is_eco) VALUES
(3, 1, 'Paris',     'Lyon',      DATE_ADD(NOW(), INTERVAL 1 DAY) + INTERVAL 8 HOUR + INTERVAL 30 MINUTE, 24.00, 4, 3, 1),
(4, 2, 'Marseille', 'Nice',      DATE_ADD(NOW(), INTERVAL 2 DAY) + INTERVAL 9 HOUR,                      18.00, 4, 2, 1),
(3, 3, 'Toulouse',  'Bordeaux',  DATE_ADD(NOW(), INTERVAL 3 DAY) + INTERVAL 17 HOUR + INTERVAL 15 MINUTE, 22.00, 3, 1, 0),
(3, 1, 'Lyon',      'Paris',     DATE_ADD(NOW(), INTERVAL 5 DAY) + INTERVAL 14 HOUR,                     24.00, 4, 4, 1),
(4, 2, 'Nice',      'Marseille', DATE_SUB(NOW(), INTERVAL 7 DAY) + INTERVAL 10 HOUR,                     18.00, 4, 0, 1);

INSERT INTO bookings (carpool_id, passenger_id, seat_count, status) VALUES
(1, 5, 1, 'pending'),
(2, 5, 2, 'accepted'),
(3, 5, 1, 'declined'),
(5, 5, 1, 'accepted');

INSERT INTO reviews (carpool_id, passenger_id, rating, comment, validated, validated_by, validated_at) VALUES
(5, 5, 5, 'Excellent trajet ! Tres confortable et ecologique. Lucas est un super conducteur.', 1, 2, NOW() - INTERVAL 5 DAY),
(1, 5, 4, 'Bon trajet, un peu en retard. Sinon tres bien.', 0, NULL, NULL);
