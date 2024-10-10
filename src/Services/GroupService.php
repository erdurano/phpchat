<?php

namespace App\Services;

use App\Repositories\GroupRepository;

class GroupService
{
    private $groupRepository;

    public function __construct()
    {
        $this->groupRepository = new GroupRepository();
    }

    public function createGroup(string $groupName)
    {
        return $this->groupRepository->createGroup($groupName);
    }

    public function listGroups()
    {
        return $this->groupRepository->listGroups();
    }

    public function joinGroup(int $groupId, string $userId)
    {
        return $this->groupRepository->joinGroup($groupId, $userId);
    }
}
