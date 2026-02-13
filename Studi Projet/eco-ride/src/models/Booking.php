<?php
// Modele Booking - gestion des reservations

require_once __DIR__ . '/../config/database.php';

class Booking
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // verifier si une reservation existe deja
    public function checkExistingBooking($carpoolId, $passengerId)
    {
        $sql = "SELECT * FROM bookings 
                WHERE carpool_id = :carpool_id 
                  AND passenger_id = :passenger_id
                  AND status IN ('pending','accepted')";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':carpool_id'   => $carpoolId,
            ':passenger_id' => $passengerId,
        ]);

        $booking = $stmt->fetch();
        return $booking ?: null;
    }

    // creer une reservation (1 place = 1 credit)
    public function createBooking($carpoolId, $passengerId)
    {
        try {
            $this->db->beginTransaction();

            // verifier le covoiturage
            $carpoolSql = "SELECT id, driver_id, price, seats_available
                           FROM carpools
                           WHERE id = :id
                           FOR UPDATE";
            $carpoolStmt = $this->db->prepare($carpoolSql);
            $carpoolStmt->execute([':id' => $carpoolId]);
            $carpool = $carpoolStmt->fetch();

            if (!$carpool) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Covoiturage introuvable.'];
            }

            if ((int)$carpool['driver_id'] === $passengerId) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Vous ne pouvez pas reserver votre propre covoiturage.'];
            }

            if ((int)$carpool['seats_available'] <= 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Plus de places disponibles.'];
            }

            // verifier les credits du passager
            $userSql = "SELECT id, credits FROM users WHERE id = :id FOR UPDATE";
            $userStmt = $this->db->prepare($userSql);
            $userStmt->execute([':id' => $passengerId]);
            $user = $userStmt->fetch();

            if (!$user) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Utilisateur introuvable.'];
            }

            if ((float)$user['credits'] < 1.0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Credits insuffisants pour reserver.'];
            }

            // verifier si deja reserve
            $existing = $this->checkExistingBooking($carpoolId, $passengerId);
            if ($existing) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Vous avez deja reserve ce covoiturage.'];
            }

            // inserer la reservation
            $insertSql = "INSERT INTO bookings (carpool_id, passenger_id, seat_count, status)
                          VALUES (:carpool_id, :passenger_id, 1, 'accepted')";
            $insertStmt = $this->db->prepare($insertSql);
            $insertStmt->execute([
                ':carpool_id'   => $carpoolId,
                ':passenger_id' => $passengerId,
            ]);

            // deduire 1 credit
            $updateUserSql = "UPDATE users SET credits = credits - 1, updated_at = NOW() WHERE id = :id";
            $this->db->prepare($updateUserSql)->execute([':id' => $passengerId]);

            // reduire le nombre de places
            $updateCarpoolSql = "UPDATE carpools SET seats_available = seats_available - 1 WHERE id = :id AND seats_available > 0";
            $updateCarpoolStmt = $this->db->prepare($updateCarpoolSql);
            $updateCarpoolStmt->execute([':id' => $carpoolId]);

            if ($updateCarpoolStmt->rowCount() === 0) {
                $this->db->rollBack();
                return ['success' => false, 'message' => 'Plus de places disponibles.'];
            }

            // recuperer le nouveau solde
            $creditStmt = $this->db->prepare("SELECT credits FROM users WHERE id = :id");
            $creditStmt->execute([':id' => $passengerId]);
            $newCredits = (float)$creditStmt->fetchColumn();

            $this->db->commit();

            return [
                'success'     => true,
                'message'     => 'Reservation confirmee ! 1 credit a ete deduit.',
                'new_credits' => $newCredits,
            ];

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erreur reservation: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Une erreur est survenue. Veuillez reessayer.',
            ];
        }
    }

    // recuperer les reservations d'un utilisateur
    public function getUserBookings($userId)
    {
        $sql = "SELECT b.*, 
                       c.from_city, c.to_city, c.departure_datetime, c.price,
                       u.pseudo AS driver_pseudo
                FROM bookings b
                INNER JOIN carpools c ON b.carpool_id = c.id
                INNER JOIN users u ON c.driver_id = u.id
                WHERE b.passenger_id = :user_id
                ORDER BY c.departure_datetime DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // annuler une reservation
    public function cancelBooking($bookingId, $userId)
    {
        try {
            $this->db->beginTransaction();

            $bookingSql = "SELECT * FROM bookings 
                           WHERE id = :id AND passenger_id = :user_id 
                           FOR UPDATE";
            $bookingStmt = $this->db->prepare($bookingSql);
            $bookingStmt->execute([':id' => $bookingId, ':user_id' => $userId]);
            $booking = $bookingStmt->fetch();

            if (!$booking || !in_array($booking['status'], ['pending', 'accepted'])) {
                $this->db->rollBack();
                return false;
            }

            // mettre a jour le statut
            $this->db->prepare("UPDATE bookings SET status = 'cancelled', updated_at = NOW() WHERE id = :id")
                     ->execute([':id' => $bookingId]);

            // rembourser 1 credit
            $this->db->prepare("UPDATE users SET credits = credits + 1, updated_at = NOW() WHERE id = :id")
                     ->execute([':id' => $userId]);

            // remettre 1 place
            $this->db->prepare("UPDATE carpools SET seats_available = seats_available + 1 WHERE id = :carpool_id")
                     ->execute([':carpool_id' => $booking['carpool_id']]);

            $this->db->commit();
            return true;

        } catch (PDOException $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log('Erreur annulation: ' . $e->getMessage());
            return false;
        }
    }
}
