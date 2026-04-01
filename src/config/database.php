<?php
// Connexion a la base de donnees
// Utilise les variables d'environement si elles existent (pour la production)

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            try {
                // on recupere les infos de connexion
                $host = getenv('DB_HOST') ?: 'localhost';
                $port = getenv('DB_PORT') ?: '3306';
                $name = getenv('DB_NAME') ?: 'ecoride';
                $user = getenv('DB_USER') ?: 'root';
                $pass = getenv('DB_PASS') ?: '';

                $dsn = "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";

                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE  => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES    => false,
                ];

                self::$connection = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log('Erreur connexion BDD: ' . $e->getMessage());
                die('Erreur de connexion a la base de donnees.');
            }
        }

        return self::$connection;
    }

    // fermer la connexion
    public static function closeConnection()
    {
        self::$connection = null;
    }
}
