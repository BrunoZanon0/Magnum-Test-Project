<?php
namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Exception;

class FipeService {
    private $client;
    private $baseUrl = 'https://parallelum.com.br/fipe/api/v1';
    private $apiKey = ''; // Para versão gratuita

    public function __construct() {
        $this->client = new Client([
            'base_uri' => $this->baseUrl,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/json',
                'User-Agent' => 'MagnumBankFIPE/1.0'
            ]
        ]);
    }

    public function getModelsVehicles($type, $codigo) {
        $response = $this->client->get($this->baseUrl . "/$type/marcas/$codigo/modelos");

        $data = json_decode($response->getBody(), true);

        if (!isset($data['modelos']) || !is_array($data['modelos'])) {
            return []; // Caso não tenha modelos
        }

        return array_map(function($model) {
            return [
                'codigo_modelo' => (string) $model['codigo'],
                'name' => $model['nome'],
            ];  
        }, $data['modelos']);
    }
    

    public function getYearsVehicles($type, $codigo, $modelo_id) {
        $response = $this->client->get($this->baseUrl . "/$type/marcas/$codigo/modelos/$modelo_id/anos");

        $data = json_decode($response->getBody(), true);

        if (!is_array($data)) {
            return []; // se não vier array, retorna vazio nao mexe nessa bagaça.
        }

        return array_map(function($year) {
            return [
                'codigo_modelo' => (string) $year['codigo'],
                'name' => $year['nome'],
            ];  
        }, $data);
    }

    public function getFullVehicle($type, $codigo, $modelo_id, $year) {
        $response = $this->client->get($this->baseUrl . "/$type/marcas/$codigo/modelos/$modelo_id/anos/$year");

        $data = json_decode($response->getBody(), true);

        if (!is_array($data)) {
            return []; 
        }

        return [
            'tipo'            => $data['TipoVeiculo'] ?? null,
            'valor'           => $data['Valor'] ?? null,
            'marca'           => $data['Marca'] ?? null,
            'modelo'          => $data['Modelo'] ?? null,
            'ano_modelo'      => $data['AnoModelo'] ?? null,
            'combustivel'     => $data['Combustivel'] ?? null,
            'codigo_fipe'     => $data['CodigoFipe'] ?? null,
            'mes_referencia'  => $data['MesReferencia'] ?? null,
            'sigla_combustivel'=> $data['SiglaCombustivel'] ?? null,
            'brand_id'        => $codigo,
            'modelo_id'       => $modelo_id,
            'ano'             => $year
        ];
    }

    /**
     * Busca todas as marcas de um tipo de veículo
     */
    public function getBrands() {
        try {
            $vehicleTypes = ['carros','motos','caminhao'];

            foreach($vehicleTypes as $vehicleType){
                $response = $this->client->get("/{$vehicleType}/marcas");
                $brands = json_decode($response->getBody(), true);
                
                return array_map(function($brand) use ($vehicleType) {
                    return [
                        'fipe_code' => (string) $brand['codigo'],
                        'name' => $brand['nome'],
                        'vehicle_type' => $vehicleType,
                        'source' => 'fipe'
                    ];
                }, $brands);
            }
            
            
        } catch (RequestException $e) {
            throw new Exception("Erro ao buscar marcas na API FIPE: " . $e->getMessage());
        }
    }

    /**
     * Busca modelos de uma marca específica
     */
    public function getModelsByBrand($brandCode, $vehicleType = 'carros') {
        try {
            $response = $this->client->get("/{$vehicleType}/marcas/{$brandCode}/modelos");
            $data = json_decode($response->getBody(), true);
            
            return array_map(function($model) use ($brandCode, $vehicleType) {
                return [
                    'fipe_code' => (string) $model['codigo'],
                    'name' => $model['nome'],
                    'brand_code' => $brandCode,
                    'vehicle_type' => $vehicleType,
                    'source' => 'fipe'
                ];
            }, $data['modelos']);
            
        } catch (RequestException $e) {
            throw new Exception("Erro ao buscar modelos na API FIPE: " . $e->getMessage());
        }
    }

    /**
     * Busca anos disponíveis para um modelo
     */
    public function getYearsByModel($brandCode, $modelCode, $vehicleType = 'carros') {
        try {
            $response = $this->client->get("/{$vehicleType}/marcas/{$brandCode}/modelos/{$modelCode}/anos");
            $years = json_decode($response->getBody(), true);
            
            return array_map(function($year) {
                return [
                    'code' => $year['codigo'],
                    'name' => $year['nome']
                ];
            }, $years);
            
        } catch (RequestException $e) {
            throw new Exception("Erro ao buscar anos na API FIPE: " . $e->getMessage());
        }
    }

    /**
     * Busca valor FIPE completo de um veículo
     */
    public function getVehicleValue($brandCode, $modelCode, $yearCode, $vehicleType = 'carros') {
        try {
            $response = $this->client->get("/{$vehicleType}/marcas/{$brandCode}/modelos/{$modelCode}/anos/{$yearCode}");
            $data = json_decode($response->getBody(), true);
            
            return [
                'value' => $data['Valor'],
                'brand' => $data['Marca'],
                'model' => $data['Modelo'],
                'year' => $data['AnoModelo'],
                'fuel' => $data['Combustivel'],
                'fipe_code' => $data['CodigoFipe'],
                'reference_month' => $data['MesReferencia'],
                'vehicle_type' => $vehicleType,
                'vehicle_type_code' => $data['TipoVeiculo'],
                'fuel_acronym' => $data['SiglaCombustivel']
            ];
            
        } catch (RequestException $e) {
            throw new Exception("Erro ao buscar valor na API FIPE: " . $e->getMessage());
        }
    }

    /**
     * Verifica status da API FIPE
     */
    public function checkApiStatus() {
        try {
            $response = $this->client->get('/carros/marcas');
            return [
                'status' => 'online',
                'timestamp' => date('Y-m-d H:i:s'),
                'response_time' => $response->getHeaderLine('x-response-time')
            ];
        } catch (RequestException $e) {
            return [
                'status' => 'offline',
                'error' => $e->getMessage(),
                'timestamp' => date('Y-m-d H:i:s')
            ];
        }
    }
}