<?php
namespace App\Controllers;

use App\Models\Vehicle;
use App\Services\CacheService;
use App\Services\FipeService;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class VehicleController {
    private $vehicleModel;
    private $cacheService;
    private $fipeService;
    private $secretKey;

    public function __construct() {
        $this->vehicleModel = new Vehicle();
        $this->cacheService = new CacheService();
        $this->secretKey = getenv('JWT_SECRET') ?: 'magnum_bank_secret_key';
        $this->fipeService = new FipeService();
    }

    public function getVehiclesByBrand($type, $brandId) {
        try {
            return json_encode($this->fipeService->getModelsVehicles($type, $brandId));
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getYearsByModels($type,$brandId, $modelo_id){
        try {
            return json_encode($this->fipeService->getYearsVehicles($type, $brandId, $modelo_id));
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function getFullVehicle($type,$brandId, $modelo_id, $year){
        // echo ;
        try {
            return json_encode($this->fipeService->getFullVehicle($type,$brandId, $modelo_id, $year));
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function updateVehicle($tipo, $fipe_code, $data) {

        if(!$data['observation']) throw new Exception('Observation é obrigatorio!');

        try {
            $updated = $this->vehicleModel->update($tipo, $fipe_code, $data['observation']);
            
            if ($updated) {
                $this->cacheService->delete("vehicle_$updated");
                
                return json_encode([
                    'status' => 'success',
                    'message' => 'Veículo atualizado com sucesso',
                    'data' => $updated
                ]);
            } else {
                http_response_code(404);
                return json_encode(['error' => 'Veículo não encontrado']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function deleteVehicle($tipo, $fipe_code) {
        try {
            $deleted = $this->vehicleModel->delete($tipo, $fipe_code);
            
            if ($deleted) {
                // Invalidar cache
                $this->cacheService->delete("vehicle_{$deleted}");
                
                return json_encode([
                    'status' => 'success',
                    'message' => 'Veículo deletado com sucesso'
                ]);
            } else {
                http_response_code(404);
                return json_encode(['error' => 'Veículo não encontrado']);
            }
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

    public function createVehicle($data, $tipoVehicle) {
        try {
            $headers = getallheaders();

            if (!isset($headers['Authorization'])) {
                throw new Exception("Token não enviado");
            }

            $token = str_replace('Bearer ', '', $headers['Authorization']);

            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            $userId = $decoded->data->user_id ?? null;

            if (!$userId) {
                throw new Exception("user_id não encontrado no token");
            }

            $data['user_id'] = $userId;
            $data['tipo']   = $tipoVehicle;
            $vehicleId = $this->vehicleModel->create($data);

            return json_encode([
                'status' => 'success',
                'message' => 'Veículo criado com sucesso',
                'data' => ['id' => $vehicleId, 'user_id' => $userId]
            ]);

        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }

}