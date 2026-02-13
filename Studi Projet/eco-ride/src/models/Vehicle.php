<?php
// Modele Vehicle - gestion des vehicules

require_once __DIR__ . '/../config/database.php';

class Vehicle
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // ajouter un vehicule
    public function addVehicle($userId, $data)
    {
        $sql = "INSERT INTO vehicles (user_id, brand, model, energy_type, seats, color, license_plate, registration_date) 
                VALUES (:user_id, :brand, :model, :energy_type, :seats, :color, :license_plate, :registration_date)";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_id'           => $userId,
            ':brand'             => $data['brand'],
            ':model'             => $data['model'],
            ':energy_type'       => $data['energy_type'],
            ':seats'             => $data['seats'],
            ':color'             => $data['color'] ?: null,
            ':license_plate'     => $data['license_plate'] ?: null,
            ':registration_date' => $data['registration_date'] ?: null
        ]);

        return (int)$this->db->lastInsertId();
    }

    // recuperer les vehicules d'un utilisateur
    public function getUserVehicles($userId)
    {
        $sql = "SELECT * FROM vehicles WHERE user_id = :user_id ORDER BY created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':user_id' => $userId]);
        return $stmt->fetchAll();
    }

    // recuperer un vehicule par son id
    public function getVehicleById($vehicleId)
    {
        $sql = "SELECT * FROM vehicles WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $vehicleId]);
        $vehicle = $stmt->fetch();
        return $vehicle ?: null;
    }

    // supprimer un vehicule
    public function deleteVehicle($vehicleId, $userId)
    {
        $sql = "DELETE FROM vehicles WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $vehicleId, ':user_id' => $userId]);
        return $stmt->rowCount() > 0;
    }

    // modifier un vehicule
    public function updateVehicle($vehicleId, $userId, $data)
    {
        $sql = "UPDATE vehicles 
                SET brand = :brand, model = :model, energy_type = :energy_type, 
                    seats = :seats, color = :color, license_plate = :license_plate, 
                    registration_date = :registration_date 
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'                => $vehicleId,
            ':user_id'           => $userId,
            ':brand'             => $data['brand'],
            ':model'             => $data['model'],
            ':energy_type'       => $data['energy_type'],
            ':seats'             => $data['seats'],
            ':color'             => $data['color'] ?: null,
            ':license_plate'     => $data['license_plate'] ?: null,
            ':registration_date' => $data['registration_date'] ?: null
        ]);

        return $stmt->rowCount() > 0;
    }

    // verifier si le vehicule est ecologique
    public function isEcoVehicle($energyType)
    {
        return in_array($energyType, ['electric', 'hybrid']);
    }
}
