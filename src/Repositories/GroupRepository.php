<?php

namespace App\Repositories;

use App\Database\DatabaseConnection;

class GroupRepository
{
    private $db;

    public function __construct()
    {
        $this->db = DatabaseConnection::getInstance();
    }

    public function createGroup(string $groupName)
    {
        $stmt = $this->db->prepare('INSERT INTO groups (group_name) VALUES (:group_name)');
        $stmt->execute(['group_name' => $groupName]);

        return ['group_id' => $this->db->lastInsertId()];
    }

    public function listGroups()
    {
        $stmt = $this->db->query('SELECT * FROM groups');
        return $stmt->fetchAll();
    }

    public function joinGroup(int $groupId, string $userId)
    {
        $stmt = $this->db->prepare('INSERT INTO group_users (group_id, user_id) VALUES (:group_id, :user_id)');
        $stmt->execute(['group_id' => $groupId, 'user_id' => $userId]);

        return ['status' => 'success'];
    }
}
