<?php

namespace App\Services;

use App\Models\MembershipModel;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelExceptions\ResourceNotFound;
use App\Models\UserModel;
use App\Services\ServiceExceptions\AlreadyMember;
use App\Services\ServiceExceptions\UserNotExists;

use function PHPUnit\Framework\isEmpty;

class MemberService

{
    private static ?self $instance = null;
    private $userModel;
    private $membershipModel;
    private $groupService;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new MemberService();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->membershipModel = new MembershipModel();
        $this->groupService = GroupService::getInstance();
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

    public function getUserById(int $id)
    {

        $user = $this->userModel->getResource(['id' => $id]);
        return $user;
    }

    public function getUserByName(string $user_name)
    {
        $user = $this->userModel->getResource(['username' => $user_name]);
        return $user;
    }

    public function subscribeUserToGroup(string $username, int $groupId): array
    {
        $user = $this->getOrCreateUser($username);
        $group = $this->groupService->getGroupById($groupId);
        try {
            $returned = $this->membershipModel->createResource(['group_id' => $groupId, 'user_id' => $user['id']]);
            if (!empty($returned)) {
                return [
                    'group_id' => $group['id'],
                    'group_name' => $group['group_name'],
                    'members' => [
                        'user_id' => $user['id'],
                        'user_name' => $user['username']
                    ]
                ];
            }
        } catch (ResourceAlreadyExists $e) {
            throw new AlreadyMember(message: sprintf(
                "'%s' is already member of '%s'.",
                $user["username"],
                $group["group_name"]
            ));
        }
    }

    public function getMembersByGroupId(int $groupId): array
    {
        $memberships = $this->membershipModel->getResource(['group_id' => $groupId]);
        $group = $this->groupService->getGroupById($groupId);
        $return_array = [
            'id' => $groupId,
            'group_name' => $group["group_name"],
            'members' => []
        ];
        foreach ($memberships as $membership) {
            $user = $this->userModel->getResource(['id' => $membership["user_id"]]);
            array_push(
                $return_array['members'],
                [
                    'id' => $user['id'],
                    'user_name' => $user['username']
                ]
            );
        }
        return $return_array;
    }

    public function IsUserMemberOfGroup($groupId, $userName)
    {
        try {
            $user = $this->getUserByName($userName);
        } catch (ResourceNotFound $e) {
            throw new UserNotExists(message: sprintf("User '%s' not found", $userName));
        }

        try {
            $return_array = $this->membershipModel->getResource(["group_id" => $groupId, "user_id" => $user["id"]]);
            if (!empty($return_array)) {
                return true;
            }
        } catch (ResourceNotFound $th) {
            return false;
        }
    }
}
