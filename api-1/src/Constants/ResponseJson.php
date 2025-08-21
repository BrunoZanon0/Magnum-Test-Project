<?php 

namespace App\ResponseNew;

use GuzzleHttp\Psr7\Response;

// Tentei criar porém não fucionou não sei prque. fica para a proxima esse enumm com os status.
class ResponseJson extends Response {

    public function __construct() {
       parent::__construct();
    }
    const HTTP_OK = 200;
    const HTTP_CREATED = 201;
    const HTTP_ACCEPTED = 202;
    const HTTP_NO_CONTENT = 204;
    
    // Códigos de erro do cliente
    const HTTP_BAD_REQUEST = 400;
    const HTTP_UNAUTHORIZED = 401;
    const HTTP_FORBIDDEN = 403;
    const HTTP_NOT_FOUND = 404;
    const HTTP_METHOD_NOT_ALLOWED = 405;
    
    // Códigos de erro do servidor
    const HTTP_INTERNAL_SERVER_ERROR = 500;
    const HTTP_SERVICE_UNAVAILABLE = 503;
    
    // Mapeamento de códigos para labels/mensagens
    private const STATUS_TEXTS = [
        self::HTTP_OK => 'OK',
        self::HTTP_CREATED => 'Created',
        self::HTTP_ACCEPTED => 'Accepted',
        self::HTTP_NO_CONTENT => 'No Content',
        
        self::HTTP_BAD_REQUEST => 'Bad Request',
        self::HTTP_UNAUTHORIZED => 'Unauthorized',
        self::HTTP_FORBIDDEN => 'Forbidden',
        self::HTTP_NOT_FOUND => 'Not Found',
        self::HTTP_METHOD_NOT_ALLOWED => 'Method Not Allowed',
        
        self::HTTP_INTERNAL_SERVER_ERROR => 'Internal Server Error',
        self::HTTP_SERVICE_UNAVAILABLE => 'Service Unavailable',
    ];

    public static function getStatusText(int $statusCode): string
    {
        return self::STATUS_TEXTS[$statusCode] ?? 'Unknown Status';
    }
    public static function getStatus(int $statusCode): array
    {
        return [
            'code' => $statusCode,
            'message' => self::getStatusText($statusCode)
        ];
    }

     public static function json(
        mixed $data, 
        int $statusCode = self::HTTP_OK, 
        array $headers = []
    ): void {
        http_response_code($statusCode);
        
        // Headers padrão para JSON
        header('Content-Type: application/json');
        
        // Headers adicionais
        foreach ($headers as $header => $value) {
            header("$header: $value");
        }
        
        echo json_encode([
            'status' => self::getStatus($statusCode),
            'data' => $data
        ]);
        
        exit; // Importante para garantir que nada mais seja executado
    }

        /**
     * Método para respostas de erro
     */
    public static function error(
        string $message, 
        int $statusCode = self::HTTP_INTERNAL_SERVER_ERROR,
        array $additionalData = []
    ): void {
        $response = [
            'error' => $message,
            'status' => self::getStatus($statusCode)
        ];
        
        if (!empty($additionalData)) {
            $response['details'] = $additionalData;
        }
        
        self::json($response, $statusCode);
    }
}