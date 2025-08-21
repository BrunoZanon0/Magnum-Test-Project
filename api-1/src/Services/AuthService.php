<?php
namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Repository\AuthRepository;
use Exception;

class AuthService {
    private $secretKey;
    private $algorithm = 'HS256';
    private AuthRepository $repository;

    public function __construct() {
        $this->secretKey = getenv('JWT_SECRET') ?: 'magnum_bank_secret_key';
        $this->repository = new AuthRepository();
    }

    public function authenticate($username, $password) {
        try {
            $isValid = $this->repository->verifyPassword($username, $password);
            
            if ($isValid) {
                $this->repository->updateLastLogin($username);
                
                return $this->generateToken($username);
            }

            return false;
            
        } catch (Exception $e) {
            error_log("Erro na autenticação: " . $e->getMessage());
            return false;
        }
    }

    public function generateToken($username) {
        try {
            $user = $this->repository->findUserByUsername($username);
            
            if (!$user) {
                throw new Exception("Usuário não encontrado");
            }

            $issuedAt = time();
            $expire = $issuedAt + (60 * 60); // 1 hora

            $payload = [
                'iat' => $issuedAt,
                'exp' => $expire,
                'iss' => 'magnum_bank_api',
                'data' => [
                    'user_id'  => is_array($user) ? $user['id'] : $user->id,
                    'username' => is_array($user) ? $user['username'] : $user->username,
                    'name'     => is_array($user) ? $user['name'] : $user->name,
                    'role'     => is_array($user) ? ($user['role'] ?? 'user') : ($user->role ?? 'user')
                ]
            ];

            return JWT::encode($payload, $this->secretKey, $this->algorithm);
            
        } catch (Exception $e) {
            error_log("Erro ao gerar token: " . $e->getMessage());
            return false;
        }
    }

    public function validateToken($token) {
        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, $this->algorithm));
            return (array) $decoded->data;
        } catch (Exception $e) {
            error_log("Token inválido: " . $e->getMessage());
            return false;
        }
    }

    public function getUsernameFromToken($token) {
        $data = $this->validateToken($token);
        return $data['username'] ?? null;
    }

    public function getUserRoleFromToken($token) {
        $data = $this->validateToken($token);
        return $data['role'] ?? null;
    }

    public function createUser($userData) {
        try {
            $existingUser = $this->repository->findUserByUsername($userData['username']);
            
            if ($existingUser) {
                throw new Exception("Usuário já existe");
            }
            $userModel = new \App\Models\Users();
            $userModel->createUser($userData);
            
            return json_encode([
                "success" => true,
                "username" => $userData['username'],
                "password" => $userData['password']
            ]);
            
        } catch (Exception $e) {
            http_response_code(400);
            return "Erro ao criar usuário: " . $e->getMessage();
        }
    }
}