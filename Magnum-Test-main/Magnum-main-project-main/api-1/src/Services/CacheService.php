<?php
namespace App\Services;

use Exception;

class CacheService {
    private $redis;
    private $useRedis = false;
    private $memoryCache = [];
    private $prefix = 'magnum:';

   public function __construct() {
        if (extension_loaded('redis')) {
            try {
                $this->redis = new \Redis();
                $host = getenv('REDIS_HOST') ?: 'redis';
                $connected = $this->redis->connect($host, 6379, 2);
                
                if ($connected) {
                    $this->useRedis = true;
                    return;
                }
            } catch (Exception $e) {
                error_log("Redis connection failed: " . $e->getMessage());
            }
        }
        
        $this->useRedis = false;
    }

    public function get($key) {
        $fullKey = $this->prefix . $key;
        
        if ($this->useRedis) {
            try {
                $value = $this->redis->get($fullKey);
                return $value ? json_decode($value, true) : null;
            } catch (Exception $e) {
                error_log("Redis get error: " . $e->getMessage());
                return null;
            }
        } else {
            // Fallback para memory cache
            if (isset($this->memoryCache[$fullKey])) {
                $data = $this->memoryCache[$fullKey];
                
                if (isset($data['expires']) && time() > $data['expires']) {
                    unset($this->memoryCache[$fullKey]);
                    return null;
                }
                
                return $data['value'];
            }
            return null;
        }
    }

    public function set($key, $value, $ttl = 3600) {
        $fullKey = $this->prefix . $key;
        
        if ($this->useRedis) {
            try {
                $serialized = json_encode($value);
                return $this->redis->setex($fullKey, $ttl, $serialized);
            } catch (Exception $e) {
                error_log("Redis set error: " . $e->getMessage());
                return false;
            }
        } else {
            // Fallback para memory cache
            $this->memoryCache[$fullKey] = [
                'value' => $value,
                'expires' => time() + $ttl
            ];
            return true;
        }
    }

    public function delete($key) {
        $fullKey = $this->prefix . $key;
        
        if ($this->useRedis) {
            try {
                return $this->redis->del($fullKey);
            } catch (Exception $e) {
                error_log("Redis delete error: " . $e->getMessage());
                return false;
            }
        } else {
            if (isset($this->memoryCache[$fullKey])) {
                unset($this->memoryCache[$fullKey]);
                return true;
            }
            return false;
        }
    }

    public function flushAll() {
        if ($this->useRedis) {
            try {
                return $this->redis->flushAll();
            } catch (Exception $e) {
                error_log("Redis flush error: " . $e->getMessage());
                return false;
            }
        } else {
            $this->memoryCache = [];
            return true;
        }
    }

    public function __destruct() {
        if ($this->useRedis && $this->redis) {
            $this->redis->close();
        }
    }
}