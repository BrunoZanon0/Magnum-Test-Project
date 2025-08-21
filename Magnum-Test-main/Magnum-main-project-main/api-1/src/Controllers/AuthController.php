<?php
namespace App\Controllers;
use App\Manager\AuthControllerManager;
use Exception;

class AuthController {
    private AuthControllerManager $manager;

    public function __construct() {
        $this->manager = new AuthControllerManager();
    }

    public function login(){
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            return $this->manager->login($data);
            
        } catch (Exception $e) {
            http_response_code(500);
            return json_encode(['error' => $e->getMessage()]);
        }
    }
}