<?php
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
ob_implicit_flush(true);

require_once __DIR__ . '/vendor/autoload.php';

use Predis\Client;
use PDO;

class Consumer {
    private $apiKey = '';
    public $db;
    private $baseUrl = 'https://parallelum.com.br/fipe/api/v1/';
    private $redis;
    
    public function __construct() {
        try {
            $this->redis = new Client([
                'host'   => 'redis',
                'port'   => 6379,
                'timeout' => 2.5
            ]);
            echo "✅ Redis conectado\n";
        } catch (Exception $e) {
            echo "❌ Erro Redis: " . $e->getMessage() . "\n";
            $this->redis = null;
        }
        
        try {
            $this->db = new PDO(
                "pgsql:host=postgres;dbname=fipe",
                "user",
                "secret",
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            echo "✅ Database conectado\n";
            
            $this->createTables();
            
        } catch (Exception $e) {
            echo "❌ Erro database: " . $e->getMessage() . "\n";
            $this->db = null;
        }
    }
    
    private function createTables() {
        $sql = "
            CREATE TABLE IF NOT EXISTS brands (
                id SERIAL PRIMARY KEY,
                fipe_code VARCHAR(50) NOT NULL UNIQUE,
                name VARCHAR(100) NOT NULL,
                type VARCHAR(20) NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            );
            
            CREATE INDEX IF NOT EXISTS idx_brands_fipe_code ON brands(fipe_code);
            CREATE INDEX IF NOT EXISTS idx_brands_type ON brands(type);
        ";
        
        $this->db->exec($sql);
        echo "✅ Tabelas verificadas/criadas\n";
    }
    
    private function makeRequest($url) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_USERAGENT => 'MagnumBankFIPE/1.0',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json'
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_FOLLOWLOCATION => true
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_error($ch)) {
            throw new Exception("cURL Error: " . curl_error($ch));
        }
        
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    private function brandExists($fipeCode) {
        $stmt = $this->db->prepare("SELECT id FROM brands WHERE fipe_code = :fipe_code");
        $stmt->execute([':fipe_code' => $fipeCode]);
        return $stmt->fetch() !== false;
    }
    
    private function insertBrand($brandData, $type) {
        try {
            if ($this->brandExists($brandData['codigo'])) {
                return false;
            }
            echo "💾 Salvando $type no banco...\n";
            
            $stmt = $this->db->prepare("
                INSERT INTO brands (codigo, fipe_code, name, type) 
                VALUES (:codigo, :fipe_code, :name, :type)
            ");

            $stmt->execute([
                ':codigo'    => $brandData['codigo'], 
                ':fipe_code' => $brandData['codigo'],
                ':name'      => $brandData['nome'],
                ':type'      => $type
            ]);

            
            echo "   ✅ Inserida: {$brandData['nome']} ({$type})\n";
            return true;
            
        } catch (Exception $e) {
            echo "   ❌ Erro ao inserir {$brandData['nome']}: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    private function saveToDatabase($fipeData) {
        $inserted = 0;
        $skipped = 0;
        
        foreach ($fipeData as $type => $brands) {            
            foreach ($brands as $brand) {
                if ($this->insertBrand($brand, $type)) {
                    $inserted++;
                } else {
                    $skipped++;
                }
            }
        }
        
        echo "📊 Resumo: $inserted novas marcas inseridas, $skipped já existiam\n";
        return $inserted;
    }
    
    public function getExterneFipe() {
        echo "🚀 Buscando dados da FIPE...\n";
        
        $vehicles = ['carros', 'motos', 'caminhoes'];
        $allData = [];
        
        foreach ($vehicles as $vehicleType) {
            try {
                echo "📦 Buscando marcas de: $vehicleType\n";
                
                $url = $this->baseUrl . $vehicleType . '/marcas';
                $result = $this->makeRequest($url);
                
                if ($result['status'] === 200 && is_array($result['data'])) {
                    $count = count($result['data']);
                    echo "✅ $vehicleType: $count marcas encontradas\n";
                    
                    $sampleBrands = array_slice($result['data'], 0, 3);
                    foreach ($sampleBrands as $brand) {
                        echo "   - {$brand['nome']} (Código: {$brand['codigo']})\n";
                    }
                    
                    $allData[$vehicleType] = $result['data'];
                } else {
                    echo "❌ Erro ao buscar $vehicleType: HTTP {$result['status']}\n";
                }
                
                sleep(2);
                
            } catch (Exception $e) {
                echo "❌ Erro em $vehicleType: " . $e->getMessage() . "\n";
            }
        }
        
        return $allData;
    }
    
    public function checkRedisData() {
        if (!$this->redis) return;
        
        try {
            echo "🔍 Verificando dados no Redis...\n";
            
            $lastUpdate = $this->redis->get('fipe_last_update');
            $fipeData = $this->redis->get('fipe_data');
            
            if ($lastUpdate) {
                echo "⏰ Última atualização: $lastUpdate\n";
            }
            
            if ($fipeData) {
                $data = json_decode($fipeData, true);
                echo "📊 Dados no Redis:\n";
                foreach ($data as $type => $brands) {
                    echo "   - $type: " . count($brands) . " marcas\n";
                }
            } else {
                echo "❌ Nenhum dado encontrado no Redis\n";
            }
            
        } catch (Exception $e) {
            echo "❌ Erro ao verificar Redis: " . $e->getMessage() . "\n";
        }
    }
    
    public function loopingRedisBrands() {
        echo "=== Consumer FIPE API ===\n";
            
        $count = 0;
        while (true) {
            $count++;
            echo "\n[" . date('Y-m-d H:i:s') . "] 🔄 Ciclo #$count\n";
            
            echo "🌐 Consultando API FIPE...\n";
            $fipeData = $this->getExterneFipe();
            
            if ($this->redis) {
                try {
                    $this->redis->set('fipe_last_update', date('Y-m-d H:i:s'));
                    $this->redis->set('fipe_data', json_encode($fipeData));
                    echo "💾 Dados salvos no Redis\n";
                } catch (Exception $e) {
                    echo "❌ Erro Redis: " . $e->getMessage() . "\n";
                }
            }
            
            if ($this->db) {
                $inserted = $this->saveToDatabase($fipeData);
                
                if ($inserted > 0) {
                    echo "🎉 $inserted novas marcas salvas no banco!\n";
                }
            }
            
            $this->checkRedisData();
            
            if ($this->db) {
                try {
                    $stmt = $this->db->query("
                        SELECT type, COUNT(*) as total 
                        FROM brands 
                        GROUP BY type
                    ");
                    $stats = $stmt->fetchAll();
                    
                    echo "📈 Estatísticas do banco:\n";
                    foreach ($stats as $stat) {
                        echo "   - {$stat['type']}: {$stat['total']} marcas\n";
                    }
                    
                } catch (Exception $e) {
                    echo "❌ Erro ao buscar estatísticas: " . $e->getMessage() . "\n";
                }
            }
            
            echo "⏳ Próxima execução em 60 segundos...\n";
            sleep(60);
        }
    }
}

try {
    $executeJob = new Consumer();
    $executeJob->loopingRedisBrands();
} catch (Exception $e) {
    echo "💥 Erro fatal: " . $e->getMessage() . "\n";
    sleep(10);
}