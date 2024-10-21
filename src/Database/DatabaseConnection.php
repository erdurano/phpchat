<?php

namespace App\Database;

use PDO;

class DatabaseConnection
{
    private static $instance = null;
    private $connection;

    public function __construct(string $databasePath = null)
    {
        if (is_null($databasePath)) {
            $this->connection = new PDO('sqlite:' . __DIR__ . '/chat.db');
        } else {
            $this->connection = new PDO('sqlite:' . $databasePath);
        }
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->createTablesIfNotExists();
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

    private function createTablesIfNotExists()
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS "groups" (
            "id" INTEGER NOT NULL,
            "group_name" varchar(50) NOT NULL UNIQUE,
            PRIMARY KEY("id" AUTOINCREMENT)
            )'
        );

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS "users" (
	        "id" INTEGER NOT NULL,
	        "username" varchar(30) NOT NULL UNIQUE,
	        PRIMARY KEY("id" AUTOINCREMENT)
            )'
        );

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS "groups_users" (
	        "id" INTEGER NOT NULL,
	        "group_id" INTEGER,
	        "user_id" INTEGER,
	        CONSTRAINT "group_id" FOREIGN KEY("group_id") REFERENCES "groups"("id") ON DELETE CASCADE,
	        CONSTRAINT "user_id" FOREIGN KEY("user_id") REFERENCES "users"("id") ON DELETE CASCADE,
	        PRIMARY KEY("id" AUTOINCREMENT),
	        CONSTRAINT "membership" UNIQUE("group_id","user_id")
            )'
        );

        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS "messages" (
	        "id"INTEGER NOT NULL,
	        "group_id"	INTEGER,
	        "user_id"	INTEGER,
	        "content"	INTEGER,
	        "created_at"	INTEGER NOT NULL DEFAULT CURRENT_TIMESTAMP,
	        PRIMARY KEY("id" AUTOINCREMENT),
	        FOREIGN KEY("group_id") REFERENCES "groups"("id") ON DELETE CASCADE,
	        FOREIGN KEY("user_id") REFERENCES "users"("id") ON DELETE CASCADE
            )'
        );
    }
}
