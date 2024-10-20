<?php

namespace App\Services;

use App\Models\GroupModel;

class GroupService
{
    private $groupRepository;

    public function __construct()
    {
        $this->groupRepository = new GroupModel();
    }

    public function createGroup(string $groupName): array
    {
        return $this->groupRepository->createGroup($groupName);
    }

    public function getGroups(): array
    {
        return $this->groupRepository->listGroups();
    }

    public function getGroupById(int $id): array
    {
        return [];
    }
}
