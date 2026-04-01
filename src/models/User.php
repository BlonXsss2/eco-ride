<?php
// Modele User - gestion des utilisateurs

require_once __DIR__ . '/../config/database.php';

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    // recuperer un utilisateur par son id
    public function getUserById($userId)
    {
        $sql = "SELECT id, email, pseudo, role, role_selection, smoking_allowed, pets_allowed, credits, created_at 
                FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    // mettre a jour le profil
    public function updateProfile($userId, $data)
    {
        $sql = "UPDATE users 
                SET pseudo = :pseudo, email = :email, role_selection = :role_selection, 
                    smoking_allowed = :smoking_allowed, pets_allowed = :pets_allowed, 
                    updated_at = NOW() 
                WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id'              => $userId,
            ':pseudo'          => $data['pseudo'],
            ':email'           => $data['email'],
            ':role_selection'  => $data['role_selection'] ?: null,
            ':smoking_allowed' => $data['smoking_allowed'] ? 1 : 0,
            ':pets_allowed'    => $data['pets_allowed'] ? 1 : 0
        ]);

        return true;
    }

    // changer le mot de passe
    public function updatePassword($userId, $hashedPassword)
    {
        $sql = "UPDATE users SET password = :password, updated_at = NOW() WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId, ':password' => $hashedPassword]);
        return $stmt->rowCount() > 0;
    }

    // verifier si l'email est unique
    public function isEmailUnique($email, $excludeUserId = 0)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email, ':id' => $excludeUserId]);
        return $stmt->fetchColumn() == 0;
    }

    // verifier si le pseudo est unique
    public function isPseudoUnique($pseudo, $excludeUserId = 0)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE pseudo = :pseudo AND id != :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':pseudo' => $pseudo, ':id' => $excludeUserId]);
        return $stmt->fetchColumn() == 0;
    }

    // recuperer le hash du mot de passe
    public function getPasswordHash($userId)
    {
        $sql = "SELECT password FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $result = $stmt->fetch();
        return $result ? $result['password'] : null;
    }
}
