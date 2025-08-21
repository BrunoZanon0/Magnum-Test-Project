<?php
namespace App\Models;

use App\Config\Database;
use PDO;
class Brand {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll() {
        $stmt = $this->db->query("SELECT name,codigo,type FROM brands ORDER BY name");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO brands (name, fipe_code) 
            VALUES (:name, :fipe_code)
        ");
        
        $stmt->execute([
            ':name' => $data['name'],
            ':fipe_code' => $data['fipe_code']
        ]);
        
        return $this->db->lastInsertId();
    }

}