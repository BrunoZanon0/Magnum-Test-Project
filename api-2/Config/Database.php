<?php
namespace App\Config;

use PDO;
use PDOException;
use Exception;

class Database {
    private static $connection = null;

    public static function getConnection() {
        if (self::$connection === null) {
            try {
                $host = getenv('DB_HOST') ?: 'postgres';
                $dbname = getenv('DB_NAME') ?: 'fipe';
                $user = getenv('DB_USER') ?: 'user';
                $pass = getenv('DB_PASS') ?: 'secret';

                self::$connection = new PDO(
                    "pgsql:host={$host};dbname={$dbname}",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                throw new Exception("Database connection failed: " . $e->getMessage());
            }
        }
        
        return self::$connection;
    }
}