<?php

namespace App\Database;

use PDO;

class DatabaseConnection
{
    private static $instance = null;
    private $connection;

    private function __construct()
    {
        $this->connection = new PDO('sqlite:' . __DIR__ . '/chatapp.db');
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new DatabaseConnection();
        }

        return self::$instance->connection;
    }
}
