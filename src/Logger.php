<?php

namespace ProxyHunter\Callback;

use PDO;

class Logger
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function logHit(string $interactionId, string $protocol, string $sourceIp, string $requestData)
    {
        $sql = "INSERT INTO interactions (interaction_id, protocol, source_ip, request_data) VALUES (:interaction_id, :protocol, :source_ip, :request_data)";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':interaction_id' => $interactionId,
                ':protocol' => $protocol,
                ':source_ip' => $sourceIp,
                ':request_data' => $requestData,
            ]);
            return true;
        } catch (\PDOException $e) {
            // In a real-world scenario, you might want to log this error to a file
            // instead of letting the script die silently.
            // For this project, we fail silently to avoid alerting the source.
            return false;
        }
    }
}
