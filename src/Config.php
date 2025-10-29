<?php

namespace ProxyHunter\Callback;

use Dotenv\Dotenv;

class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->config = [
            'db_driver' => $_ENV['DB_DRIVER'] ?? 'mysql',
            'db_host' => $_ENV['DB_HOST'] ?? null,
            'db_port' => $_ENV['DB_PORT'] ?? null,
            'db_database' => $_ENV['DB_DATABASE'] ?? null,
            'db_username' => $_ENV['DB_USERNAME'] ?? null,
            'db_password' => $_ENV['DB_PASSWORD'] ?? null,
            'api_key' => $_ENV['API_KEY'] ?? null,
        ];
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Config();
        }
        return self::$instance;
    }

    public function get($key)
    {
        return $this->config[$key] ?? null;
    }
}
