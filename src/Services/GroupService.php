<?php

namespace App\Services;

use App\Models\GroupModel;

class GroupService
{
    private static self $instance = null;
    private $groupModel;

    public function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new GroupService();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->groupModel = new GroupModel();
    }

    public function createGroup(string $groupName): array
    {
        return $this->groupModel->createResource(['group_name' => $groupName]);
    }

    public function getGroups(): array
    {
        return $this->groupModel->getResource();
    }

    public function getGroupById(int $id): array
    {
        return $this->groupModel->getResource(['id' => $id]);
    }
}
