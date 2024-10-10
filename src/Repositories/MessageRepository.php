<?php

namespace App\Repositories;

use App\Database\DatabaseConnection;

class MessageRepository
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function sendMessage(int $groupId, string $userId, string $message)
    {
        $stmt = $this->db->prepare('INSERT INTO messages (group_id, user_id, message) VALUES (:group_id, :user_id, :message)');
        $stmt->execute(['group_id' => $groupId, 'user_id' => $userId, 'message' => $message]);

        return ['message_id' => $this->db->lastInsertId()];
    }

    public function listMessages(int $groupId)
    {
        $stmt = $this->db->prepare('SELECT * FROM messages WHERE group_id = :group_id');
        $stmt->execute(['group_id' => $groupId]);

        return $stmt->fetchAll();
    }
}
