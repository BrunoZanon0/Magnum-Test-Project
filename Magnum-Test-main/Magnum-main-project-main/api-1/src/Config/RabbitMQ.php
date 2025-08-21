<?php
namespace App\Config;

class RabbitMQ {
    public static function getConfig() {
        return [
            'host' => getenv('RABBITMQ_HOST') ?: 'rabbitmq',
            'port' => 5672,
            'user' => 'guest',
            'password' => 'guest',
            'vhost' => '/',
            'queues' => [
                'fipe_brands' => [
                    'durable' => true,
                    'auto_delete' => false
                ],
                'fipe_models' => [
                    'durable' => true,
                    'auto_delete' => false
                ]
            ]
        ];
    }
}