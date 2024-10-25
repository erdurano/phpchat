<?php

namespace App\Services;

use App\Models\GroupModel;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use App\Services\ServiceExceptions\GroupAlreadyExists;
use App\Services\ServiceExceptions\GroupNotFound;

class GroupService
{
    private static ?self $instance = null;
    private $groupModel;

    public static function getInstance(): self
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
        try {
            return $this->groupModel->createResource(['group_name' => $groupName]);
        } catch (ResourceAlreadyExists $th) {
            throw new GroupAlreadyExists(message: sprintf("A group named '%s' already exists", $groupName));
        }
    }

    public function getGroups(): array
    {
        try {
            return $this->groupModel->getResource();
        } catch (ResourceNotFound $e) {
            throw new GroupNotFound(message: "There are no groups in this application yet");
        }
    }

    public function getGroupById(int $id): array
    {
        return $this->groupModel->getResource(['id' => $id]);
    }
}
