<?php

namespace App\Database;

use PDO;

class DatabaseConnection
{
    private static $instance = null;
    private $connection;
    private ?string $databasePath = null;

    public function __construct(string $databasePath = null)
    {
        if (is_null($databasePath)) {
            $this->connection = new PDO('sqlite:' . __DIR__ . '/../../chat.db');
        } else {
            $this->connection = new PDO('sqlite:' . $databasePath);
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function getConnection()
    {
        return $this->connection;
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DatabaseConnection();
        }

        return self::$instance->connection;
    }
}
