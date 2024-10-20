<?php

namespace App\Services;

use App\Models\MessageModel;
use App\Repositories\MessageRepository;

class MessageService
{
    private $messageRepository;

    public function __construct()
    {
        $this->messageRepository = new MessageModel();
    }

    public function sendMessage(int $groupId, string $userId, string $message)
    {
        return $this->messageRepository->sendMessage($groupId, $userId, $message);
    }

    public function listMessages(int $groupId, ?string $since): array
    {
        return $this->messageRepository->listMessages($groupId);
    }
}
