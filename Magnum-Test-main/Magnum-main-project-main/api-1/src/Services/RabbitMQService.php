<?php
namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class RabbitMQService {
    private $connection;
    private $channel;

    public function __construct() {
        $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
        $this->connect($host);
    }

    private function connect($host) {
        try {
            $this->connection = new AMQPStreamConnection(
                $host,
                5672,
                'guest',
                'guest'
            );
            
            $this->channel = $this->connection->channel();
            
        } catch (\Exception $e) {
            throw new \Exception("Erro ao conectar com RabbitMQ: " . $e->getMessage());
        }
    }

    public function consume($queueName, $callback) {
        try {
            $this->channel->queue_declare($queueName, false, true, false, false);
            $this->channel->basic_consume($queueName, '', false, true, false, false, $callback);
            
            while ($this->channel->is_consuming()) {
                $this->channel->wait();
            }
            
        } catch (\Exception $e) {
            throw new \Exception("Erro ao consumir fila: " . $e->getMessage());
        }
    }

    public function __destruct() {
        if ($this->channel) {
            $this->channel->close();
        }
        if ($this->connection) {
            $this->connection->close();
        }
    }
}