<?php

namespace ProxyHunter\\Callback;

use PDO;

class ApiHandler
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Fetches all interaction records for a given interaction ID.
     *
     * @param string $interactionId The unique ID to search for.
     * @return array An array of interaction records, or an empty array if none are found.
     */
    public function getHits(string $interactionId): array
    {
        $sql = "SELECT protocol, source_ip, request_data, timestamp FROM interactions WHERE interaction_id = :interaction_id ORDER BY timestamp ASC";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([':interaction_id' => $interactionId]);
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Decode the JSON data for each result
            foreach ($results as &$result) {
                $result['request_data'] = json_decode($result['request_data'], true);
            }

            return $results;
        } catch (\\PDOException $e) {
            // In a real app, log this error. For now, return an empty array on failure.
            return [];
        }
    }
}
