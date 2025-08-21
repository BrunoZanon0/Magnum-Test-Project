<?php
namespace App\Models;

use App\Config\Database;
use PDO;
use Exception;

class Users {
    private $db;
    private $table = 'users';

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByUsername($username) {
        try {
            $stmt = $this->db->prepare("
                SELECT id,name,password 
                FROM {$this->table} 
                WHERE name = :username
            ");
            
            $stmt->execute([':username' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar usuário por username: " . $e->getMessage());
        }
    }

    public function updateLastLogin($username) {
        try {
            $stmt = $this->db->prepare("
                UPDATE {$this->table} 
                SET updated_at = NOW() 
                WHERE name = :username
            ");
            
            return $stmt->execute([':username' => $username]);
            
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar último login: " . $e->getMessage());
        }
    }

    public function createUser($userData) {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO {$this->table} (name, password) 
                VALUES (:name, :password)
            ");
            
            return $stmt->execute([
                ':name' => $userData['username'],
                ':password' => password_hash($userData['password'], PASSWORD_DEFAULT),
            ]);
            
        } catch (Exception $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }
}