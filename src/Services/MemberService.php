<?php

namespace App\Services;

use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\UserModel;

class MemberService

{
    private static self $instance = null;
    private $userModel;

    public function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new MemberService();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->userModel = new UserModel();
    }


    public function getOrCreateUser(string $username): array
    {
        try {

            $user = $this->userModel->createResource(['username' => $username]);
        } catch (ResourceAlreadyExists $e) {
            $user = $this->userModel->getResource(['username' => $username]);
        }
        return $user;
    }

    public function subscribeUserToGroup(string $username, int $gropId): array
    {
        $user = $this->getOrCreateUser($username);
    }

    public function getMembersByGroupId(int $groupId): array
    {
        return [];
    }
}
