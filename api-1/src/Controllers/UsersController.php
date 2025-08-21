<?php
namespace App\Controllers;

use App\Services\AuthService;
use Exception;

class UsersController {
    private AuthService $authService;

    public function __construct() {
        $this->authService = new AuthService();
    }

    public function cadastro(){

        $data = json_decode(file_get_contents('php://input'), true);

        return $this->authService->createUser($data);
    }
}