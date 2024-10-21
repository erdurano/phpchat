<?php

namespace App\Services;

use App\Models\MessageModel;
use App\Repositories\MessageRepository;

class MessageService
{
    private static ?self $instance = null;
    private $messageModel = null;
    private $groupService = null;
    private $memberService = null;

    public static function getInstance(): self
    {
        if (is_null(self::$instance)) {
            self::$instance = new MessageService();
        }
        return self::$instance;
    }

    public function __construct()
    {
        $this->messageModel = new MessageModel();
        $this->groupService = GroupService::getInstance();
        $this->memberService = MemberService::getInstance();
    }


    public function sendMessage(int $groupId, string $userName, string $message)
    {
        $user = $this->memberService->getOrCreateUser($userName);
        $group = $this->groupService->getGroupById($groupId);
        $message_array = $this->messageModel->createResource([
            'group_id' => $groupId,
            'user_id' => $user['id'],
            'content' => $message
        ]);
        $return_array = [
            'group_id' => $group['id'],
            'group_name' => $group['group_name'],
            'message' => [
                'sender' => [
                    'id' => $user['id'],
                    'user_name' => $user['username']
                ],
                'content' => $message_array['content'],
                'created_at' => $message_array['created_at']
            ]
        ];

        return $return_array; // TODO: fit json format to return array
    }

    public function listMessages(int $groupId, ?string $since): array
    {
        $group = $this->groupService->getGroupById($groupId);

        $params = ['group_id' => $groupId];
        if (!is_null($since)) {
            $params['since'] = $since;
        }
        $messages = $this->messageModel->getResource($params);
        $message_array = [
            'group_id' => $group['id'],
            'group_name' => $group['group_name'],
            'messages' => []
        ];
        foreach ($messages as $message) {
            $sender = $this->memberService->getUserById($message['user_id']); // TODO:
            array_push($message_array['messages'], [
                'sender' => [
                    'id' => $sender['id'],
                    'user_name' => $sender['username']
                ],
                'content' => $message['content'],
                'created_at' => $message['created_at']
            ]);
        }
        return $message_array;
    }
}
