<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use Exception;

use App\Database\Connection;

class Database
{
    public function __construct(
        protected Connection $connection,
        protected PDO $db
    ) {
        $this->db = $connection->getConnection();
    }

    // =========================================
    // QUERY LARGE DATA SETS
    // =========================================
    protected function queryAll(
        string $sql, 
        array $params = []
    ): array {

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll();
    }

    // =========================================
    // QUERY SINGLE DATA SET
    // =========================================
    protected function queryOne(
        string $sql, 
        array $params = []
    ): ?array {

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetch();

        return $result ?: null;
    }

    // =========================================
    // EXECUTE QUERY
    // =========================================
    protected function executeQuery(
        string $sql, 
        array $params = []
    ): bool {

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    // =========================================
    // FETCH COLUMN DATA
    // =========================================
    protected function fetchColumn(
        string $sql, 
        array $params = []
    ): ?int {

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $result = $stmt->fetchColumn();

        return $result !== false ? (int) $result : 0;
    }

    // =========================================
    // GET LAST INSERTED DATA ID
    // =========================================
    protected function lastInsertId(): string
    {
        return $this->db->lastInsertId();
    }

    // =========================================
    // BEGIN TRANSACTION
    // =========================================
    protected function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    // =========================================
    // COMMIT TRANSACTION
    // =========================================
    protected function commit(): bool
    {
        return $this->db->commit();
    }

    // =========================================
    // ROLLBACK TRANSACTION
    // =========================================
    protected function rollback(): bool
    {
        return $this->db->rollBack();
    }

    // =========================================
    // RUN TRANSACTION
    // =========================================
    protected function transaction(callable $callback)
    {
        try {

            $this->beginTransaction();

            $result = $callback($this->db);

            $this->commit();

            return $result;

        } catch (Exception $e) {

            $this->rollback();

            throw $e;
        }
    }
}