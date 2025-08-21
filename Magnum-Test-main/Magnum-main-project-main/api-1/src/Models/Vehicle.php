<?php
namespace App\Models;

use App\Config\Database;
use Exception;
use PDO;
class Vehicle {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getByBrand($brandId) {
        $stmt = $this->db->prepare("
            SELECT v.*, b.name as brand_name 
            FROM vehicles v 
            JOIN brands b ON v.brand_id = b.id 
            WHERE v.brand_id = :brand_id 
            ORDER BY v.model
        ");
        
        $stmt->execute([':brand_id' => $brandId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($tipo , $fipe_code, $observation) {
        $stmt = $this->db->prepare("SELECT id FROM vehicles WHERE tipo = :tipo AND fipe_code = :fipe_code");
        $stmt->execute([
            ':tipo' => $tipo,
            ':fipe_code' => $fipe_code
        ]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            return null; 
        }

        $stmt = $this->db->prepare("
            UPDATE vehicles 
            SET observations = :observations, updated_at = NOW() 
            WHERE id = :id
        ");
        $stmt->execute([
            ':observations' => $observation ?? null,
            ':id' => $vehicle['id']
        ]);

        return $vehicle['id'];
    }


    public function delete($tipo, $fipe_code) {
        $stmt = $this->db->prepare("SELECT id FROM vehicles WHERE fipe_code = :fipe_code AND tipo = :tipo");
        $stmt->execute([
            ':fipe_code' => $fipe_code,
            ':tipo' => $tipo
        ]);
        $vehicle = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vehicle) {
            return null;
        }

        $stmt = $this->db->prepare("DELETE FROM vehicles WHERE id = :id");
        $stmt->execute([':id' => $vehicle['id']]);

        if ($stmt->rowCount() === 0) {
            return null; 
        }

        return $vehicle['id']; 
    }

    public function checkVehicle($fipe_code){
        $stmt = $this->db->prepare("SELECT * FROM vehicles WHERE fipe_code = :fipe_code");
        $stmt->execute([':fipe_code' => $fipe_code]);

        return $stmt->fetchAll();

    }

    public function create($data) {

        $teste = $this->checkVehicle($data['codigo_fipe']);
        if($teste){
            throw new Exception("Registro já existente");
        }

        $stmt = $this->db->prepare("
            INSERT INTO vehicles (
            brand_id, 
            model, 
            year, 
            fipe_code, 
            marca,
            valor,
            user_id,
            tipo,
            ano_modelo,
            sigla_combustivel,
            modelo_id,
            ano,
            mes_referencia,
            observations) 
            VALUES (
            :brand_id, 
            :model, 
            :year, 
            :fipe_code, 
            :marca,
            :valor,
            :user_id,
            :tipo,
            :ano_modelo,
            :combustivel,
            :modelo_id,
            :ano,
            :mes_referencia,
            :observations)
        ");
        
        $stmt->execute([
            ':brand_id' => $data['brand_id'],
            ':model' => $data['modelo'],
            ':year' => $data['ano_modelo'] ?? null,
            ':fipe_code' => $data['codigo_fipe'],
            ':marca' => $data['marca'] ?? null,
            ':valor' => $data['valor'] ?? null,
            ':user_id' => $data['user_id'] ?? null,
            ':tipo' => $data['tipo'] ?? null,
            ':ano_modelo' => $data['ano_modelo'] ?? null,
            ':combustivel' => $data['combustivel'] ?? null,
            ':modelo_id' => $data['modelo_id'] ?? null,
            ':ano' => $data['ano'] ?? null,
            ':mes_referencia' => $data['mes_referencia'] ?? null,
            ':observations' => $data['observations'] ?? null
        ]);
        
        return $this->db->lastInsertId();
    }
}