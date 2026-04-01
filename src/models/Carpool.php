<?php
// Modele Carpool - gestion des covoiturages

require_once __DIR__ . '/../config/database.php';

class Carpool
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // rechercher des covoiturages
    public function searchCarpools($from, $to, $date = null, $filters = [])
    {
        $sql = "SELECT c.*, 
                       u.pseudo as driver_pseudo,
                       v.brand, v.model, v.energy_type,
                       COALESCE(AVG(r.rating), 0) as driver_rating,
                       COUNT(DISTINCT r.id) as review_count
                FROM carpools c
                INNER JOIN users u ON c.driver_id = u.id
                LEFT JOIN vehicles v ON c.vehicle_id = v.id
                LEFT JOIN reviews r ON r.carpool_id = c.id AND r.validated = 1
                WHERE c.seats_available > 0
                  AND c.from_city LIKE :from_city
                  AND c.to_city LIKE :to_city";

        $params = [
            ':from_city' => '%' . $from . '%',
            ':to_city'   => '%' . $to . '%'
        ];

        // filtre par date
        if ($date) {
            $sql .= " AND DATE(c.departure_datetime) = :date";
            $params[':date'] = $date;
        } else {
            $sql .= " AND c.departure_datetime >= NOW()";
        }

        // filtre eco uniquement
        if (!empty($filters['eco_only']) && $filters['eco_only'] == '1') {
            $sql .= " AND c.is_eco = 1";
        }

        // filtre prix max
        if (!empty($filters['max_price'])) {
            $sql .= " AND c.price <= :max_price";
            $params[':max_price'] = (float)$filters['max_price'];
        }

        $sql .= " GROUP BY c.id";

        // filtre note minimum
        if (!empty($filters['min_rating'])) {
            $sql .= " HAVING driver_rating >= :min_rating";
            $params[':min_rating'] = (int)$filters['min_rating'];
        }

        $sql .= " ORDER BY c.departure_datetime ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    // details d'un covoiturage
    public function getCarpoolDetails($carpoolId)
    {
        $sql = "SELECT c.*, 
                       u.pseudo as driver_pseudo, u.email as driver_email,
                       u.smoking_allowed, u.pets_allowed,
                       v.brand, v.model, v.energy_type, v.seats as vehicle_seats,
                       v.color, v.license_plate,
                       COALESCE(AVG(r.rating), 0) as driver_rating,
                       COUNT(DISTINCT r.id) as review_count
                FROM carpools c
                INNER JOIN users u ON c.driver_id = u.id
                LEFT JOIN vehicles v ON c.vehicle_id = v.id
                LEFT JOIN reviews r ON r.carpool_id = c.id AND r.validated = 1
                WHERE c.id = :id
                GROUP BY c.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $carpoolId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    // infos sur le conducteur
    public function getDriverInfo($driverId)
    {
        $sql = "SELECT u.id, u.pseudo, u.email, u.smoking_allowed, u.pets_allowed,
                       COALESCE(AVG(r.rating), 0) as avg_rating,
                       COUNT(DISTINCT r.id) as total_reviews
                FROM users u
                LEFT JOIN carpools c ON c.driver_id = u.id
                LEFT JOIN reviews r ON r.carpool_id = c.id AND r.validated = 1
                WHERE u.id = :id
                GROUP BY u.id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $driverId]);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public function updateSeats($carpoolId, $seats)
    {
        $sql = "UPDATE carpools SET seats_available = :seats WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $carpoolId, ':seats' => $seats]);
        return $stmt->rowCount() > 0;
    }

    // recuperer les avis d'un covoiturage
    public function getReviews($carpoolId)
    {
        $sql = "SELECT r.rating, r.comment, r.created_at,
                       u.pseudo as reviewer_pseudo
                FROM reviews r
                INNER JOIN users u ON r.passenger_id = u.id
                WHERE r.carpool_id = :carpool_id
                  AND r.validated = 1
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':carpool_id' => $carpoolId]);
        return $stmt->fetchAll();
    }

    // avis du conducteur (tous les covoiturages)
    public function getDriverReviews($driverId)
    {
        $sql = "SELECT r.rating, r.comment, r.created_at,
                       u.pseudo as reviewer_pseudo,
                       c.from_city, c.to_city
                FROM reviews r
                INNER JOIN carpools c ON r.carpool_id = c.id
                INNER JOIN users u ON r.passenger_id = u.id
                WHERE c.driver_id = :driver_id
                  AND r.validated = 1
                ORDER BY r.created_at DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':driver_id' => $driverId]);
        return $stmt->fetchAll();
    }

    // trouver les covoiturages les plus proches (suggestions)
    public function findNearestCarpools($from, $to, $limit = 5)
    {
        $limit = max(1, min(50, (int)$limit));

        $sql = "SELECT c.*, 
                       u.pseudo as driver_pseudo,
                       v.brand, v.model, v.energy_type,
                       COALESCE(AVG(r.rating), 0) as driver_rating,
                       COUNT(DISTINCT r.id) as review_count,
                       ABS(DATEDIFF(c.departure_datetime, NOW())) as days_diff
                FROM carpools c
                INNER JOIN users u ON c.driver_id = u.id
                LEFT JOIN vehicles v ON c.vehicle_id = v.id
                LEFT JOIN reviews r ON r.carpool_id = c.id AND r.validated = 1
                WHERE c.seats_available > 0
                  AND c.from_city LIKE :from_city
                  AND c.to_city LIKE :to_city
                  AND c.departure_datetime >= NOW()
                GROUP BY c.id
                ORDER BY days_diff ASC, c.departure_datetime ASC
                LIMIT " . $limit;

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':from_city' => '%' . $from . '%',
            ':to_city'   => '%' . $to . '%'
        ]);
        return $stmt->fetchAll();
    }
}
