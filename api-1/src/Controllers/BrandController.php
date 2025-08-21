<?php
namespace App\Controllers;

use App\Models\Brand;
use App\Services\FipeService;
use App\Services\RabbitMQService;
use Exception;

class BrandController {
    private $brandModel;
    private $fipeService;
    private $rabbitMQService;

    public function __construct() {
        $this->brandModel = new Brand();
        $this->fipeService = new FipeService();
        $this->rabbitMQService = new RabbitMQService();
    }

    public function getAllBrands() {
        try {
            $brands = $this->brandModel->getAll();
            
            return json_encode([
                'status' => 'success',
                'data' => $brands
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Novo endpoint para status da API FIPE
     */
    public function getFipeStatus() {
        try {
            $status = $this->fipeService->checkApiStatus();
            
            return json_encode([
                'status' => 'success',
                'data' => $status
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /**
     * Novo endpoint para busca direta na FIPE
     */
    public function searchFipe($vehicleType, $brandCode = null, $modelCode = null, $yearCode = null) {
        try {
            if ($yearCode && $modelCode && $brandCode) {
                // Buscar valor específico
                $data = $this->fipeService->getVehicleValue($brandCode, $modelCode, $yearCode, $vehicleType);
            } elseif ($modelCode && $brandCode) {
                // Buscar anos do modelo
                $data = $this->fipeService->getYearsByModel($brandCode, $modelCode, $vehicleType);
            } elseif ($brandCode) {
                // Buscar modelos da marca
                $data = $this->fipeService->getModelsByBrand($brandCode, $vehicleType);
            } 
            
            return json_encode([
                'status' => 'success',
                'data' => $data
            ]);
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
}