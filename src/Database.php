<?php

namespace ProxyHunter\Callback;

use PDO;
use PDOException;

class Database
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $config = Config::getInstance();

        $driver = $config->get('db_driver') ?? 'mysql';

        try {
            if ($driver === 'sqlite') {
                // For sqlite, db_database is a filesystem path to the sqlite file
                $sqlitePath = $config->get('db_database') ?? __DIR__ . '/../data/hunter_test.sqlite';

                // Resolve relative paths against project root to avoid cwd issues
                $projectRoot = realpath(__DIR__ . '/..');
                if ($projectRoot === false) {
                    throw new PDOException('Could not determine project root for sqlite path resolution');
                }
                if (!preg_match('#^/#', $sqlitePath)) {
                    $sqlitePath = $projectRoot . '/' . ltrim($sqlitePath, './');
                }

                $dir = dirname($sqlitePath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0777, true);
                }
                if (!file_exists($sqlitePath)) {
                    // create empty sqlite file with permissive permissions for testing
                    touch($sqlitePath);
                    @chmod($sqlitePath, 0666);
                }

                $dsn = "sqlite:" . $sqlitePath;
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ];
                $this->connection = new PDO($dsn, null, null, $options);
            } else {
                $host = $config->get('db_host');
                $port = $config->get('db_port');
                $dbname = $config->get('db_database');
                $username = $config->get('db_username');
                $password = $config->get('db_password');

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";
                $options = [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

                $this->connection = new PDO($dsn, $username, $password, $options);
            }
        } catch (PDOException $e) {
            // In a real app, you'd log this error, not just die
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->connection;
    }
}
