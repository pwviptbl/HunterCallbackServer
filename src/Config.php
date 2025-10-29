<?php

namespace ProxyHunter\\Callback;

use Dotenv\\Dotenv;

class Config
{
    private static $instance = null;
    private $config = [];

    private function __construct()
    {
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
        $dotenv->load();

        $this->config = [
            'db_host' => $_ENV['DB_HOST'],
            'db_port' => $_ENV['DB_PORT'],
            'db_database' => $_ENV['DB_DATABASE'],
            'db_username' => $_ENV['DB_USERNAME'],
            'db_password' => $_ENV['DB_PASSWORD'],
            'api_key' => $_ENV['API_KEY'],
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
