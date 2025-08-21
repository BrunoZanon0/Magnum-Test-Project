<?php 

namespace App\Manager;

use App\Services\AuthService;
use App\Interfaces\AuthControllerInterface;

class AuthControllerManager implements AuthControllerInterface{
    private $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function login($data): bool|string{

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';
        
        $token = $this->authService->authenticate($username, $password);
        
        if ($token) {
            return json_encode([
                'status' => 'success',
                'token' => $token
            ]);
        } else {
            http_response_code(401);
            return json_encode(['error' => 'Credenciais inválidas']);
        }
    }

}