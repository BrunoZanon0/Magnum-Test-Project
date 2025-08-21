<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE & ~E_STRICT & ~E_WARNING);
ini_set('display_errors', 0);
date_default_timezone_set('America/Sao_Paulo');

require_once __DIR__ . '/../vendor/autoload.php';

use App\Controllers\BrandController;
use App\Controllers\VehicleController;
use App\Controllers\AuthController;
use App\Middleware\AuthMiddleware;
use App\Middleware\CorsMiddleware;
use App\Controllers\UsersController;

header("Content-Type: application/json");

CorsMiddleware::handle();

spl_autoload_register(function ($className) {
    $className = str_replace("App\\", "", $className);
    $filePath = __DIR__ . '/' . str_replace('\\', '/', $className) . '.php';
    
    if (file_exists($filePath)) {
        require_once $filePath;
    }
});

$brandController = new BrandController();
$vehicleController = new VehicleController();
$authController = new AuthController();
$userController = new UsersController();

// Obter método e URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

// Rotas da API
$route = isset($uri[2]) ? $uri[2] : '';
$param1 = isset($uri[3]) ? $uri[3] : '';
$param2 = isset($uri[4]) ? $uri[4] : '';
$param3 = isset($uri[5]) ? $uri[5] : '';
$param4 = isset($uri[6]) ? $uri[6] : '';
$param5 = isset($uri[7]) ? $uri[7] : '';
$param6 = isset($uri[8]) ? $uri[8] : '';


try {
    if ($method === 'POST' && $route === 'auth') {
        echo $authController->login();
        exit;
    }

    if($method === 'POST' && $route === 'new-user'){
        echo $userController->cadastro();
        exit;
    }

    AuthMiddleware::handle();

    switch (true) {
        // Brands
           
        case $method === 'GET' && $route === 'brands':
            echo $brandController->getAllBrands();
            break;
            
        // Vehicles

        case $method === 'GET' && $route === 'vehicles' && !empty($param1) && !empty($param2) && !empty($param3) && !empty($param4) && !empty($param5) && !empty($param6):

            echo $vehicleController->getFullVehicle($param1, $param2, $param4, $param6);
            break;
        case $method === 'GET' && $route === 'vehicles' && !empty($param1) && !empty($param2) && !empty($param3) && !empty($param4) && !empty($param5):
            echo $vehicleController->getYearsByModels($param1, $param2, $param4);
            break;

        case $method === 'GET' && $route === 'vehicles' && !empty($param1) && !empty($param2):
            echo $vehicleController->getVehiclesByBrand($param1, $param2);
            break;
            
        case $method === 'PUT' && $route === 'vehicles' && !empty($param1) && !empty($param2):
            $data = json_decode(file_get_contents('php://input'), true);
            echo $vehicleController->updateVehicle($param1, $param2, $data);
            break;
            
        case $method === 'DELETE' && $route === 'vehicles' && !empty($param1) && !empty($param2):
            echo $vehicleController->deleteVehicle($param1, $param2);
            break;
            
        case $method === 'POST' && $route === 'vehicles' && !empty($param1):
            $data = json_decode(file_get_contents('php://input'), true);
            echo $vehicleController->createVehicle($data, $param1);
            break;
                
        case $method === 'GET' && preg_match('/\/api\/fipe-status/', $request):
            AuthMiddleware::handle();
            echo (new BrandController())->getFipeStatus();
            break;

        case $method === 'GET' && preg_match('/\/api\/fipe-search\/(carros|motos|caminhoes)(?:\/(\d+)(?:\/(\d+)(?:\/(.+))?)?)?/', $request, $matches):
            AuthMiddleware::handle();
            $vehicleType = $matches[1];
            $brandCode = $matches[2] ?? null;
            $modelCode = $matches[3] ?? null;
            $yearCode = $matches[4] ?? null;
            echo (new BrandController())->searchFipe($vehicleType, $brandCode, $modelCode, $yearCode);
            break;
            
        case $method === 'GET' && $route === 'health':
            echo json_encode([
                'status' => 'Funcionando Perfeitamente!', 
                'service' => 'API-1',
                'timestamp' => date('Y-m-d H:i:s')
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode(['error' => 'Endpoint não encontrado']);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}