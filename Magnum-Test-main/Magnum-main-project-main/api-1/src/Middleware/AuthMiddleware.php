<?php
namespace App\Middleware;

use App\Services\AuthService;

class AuthMiddleware {
    public static function handle() {
        $authService = new AuthService();
        
        if ($_SERVER['REQUEST_URI'] === '/api/health') {
            return true;
        }
        
        $headers = getallheaders();
        $token = isset($headers['Authorization']) ? 
                 str_replace('Bearer ', '', $headers['Authorization']) : null;

        if (!$token || !$authService->validateToken($token)) {
            http_response_code(401);
            echo json_encode(['error' => 'Token de autenticação inválido']);
            exit();
        }
        
        return true;
    }
}