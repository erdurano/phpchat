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


    public function sendMessage(int $groupId, string $userId, string $message)
    {
        return $this->messageModel->createResource([
            'group_id' => $groupId,
            'user_id' => $userId,
            'content' => $userId
        ]);
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
            // sender = $this->memberService->getOrCreateUser('erdurano'); // TODO:
            array_push($message_array['messages'], $message);
        }
        return $message_array;
    }
}
