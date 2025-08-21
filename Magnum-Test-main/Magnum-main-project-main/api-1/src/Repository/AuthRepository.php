<?php 
namespace App\Repository;

use App\Interfaces\AuthRepositoryInterface;
use App\Models\Users;
use Exception;

class AuthRepository implements AuthRepositoryInterface {
    private $userModel;

    public function __construct() {
        $this->userModel = new Users();
    }

    public function findUserByUsername($username): mixed {
        try {
            return $this->userModel->findByUsername($username);
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        }
    }

    public function verifyPassword($username, $password): bool {
        try {
            $user = $this->findUserByUsername($username);
            
            if (!$user) {
                return false;
            }

            return password_verify($password, $user['password']);
        } catch (Exception $e) {
            throw new Exception("Erro ao verificar senha: " . $e->getMessage());
        }
    }

    public function updateLastLogin($username): bool {
        try {
            return $this->userModel->updateLastLogin($username);
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar último login: " . $e->getMessage());
        }
    }
}