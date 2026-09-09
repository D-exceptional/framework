<?php

declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use Exception;

class Connection
{
    protected ?PDO $sharedConnection = null;

    public function __construct()
    {
        if ($this->sharedConnection === null) {
            $this->sharedConnection = $this->connect();
        }
    }

    // =========================================
    // ESTABLISH DATABASE CONNECTION
    // =========================================
    protected function connect(): PDO
    {
        $host = config('database.mysql.host', 'mysql');
        $db   = config('database.mysql.name', '');
        $user = config('database.mysql.user', '');
        $pass = config('database.mysql.pass', '');

        if (!$db || !$user) {
            throw new Exception(
                'Database environment variables not set.'
            );
        }

        $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

        try {

            return new PDO(
                $dsn,
                $user,
                $pass,
                [
                    PDO::ATTR_ERRMODE =>
                        PDO::ERRMODE_EXCEPTION,

                    PDO::ATTR_DEFAULT_FETCH_MODE =>
                        PDO::FETCH_ASSOC,

                    PDO::ATTR_PERSISTENT => true
                ]
            );

        } catch (PDOException $e) {

            throw new Exception(
                'Database connection failed: ' .
                $e->getMessage()
            );
        }
    }

    // =========================================
    // GET DATABASE CONNECTION INSTANCE
    // =========================================
    public function getConnection(): PDO
    {
        return $this->sharedConnection;
    }
}