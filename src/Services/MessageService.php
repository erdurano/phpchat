<?php

namespace App\Services;

use App\Repositories\MessageRepository;

class MessageService
{
    private $messageRepository;

    public function __construct()
    {
        $this->messageRepository = new MessageRepository();
    }

    public function sendMessage(int $groupId, string $userId, string $message)
    {
        return $this->messageRepository->sendMessage($groupId, $userId, $message);
    }

    public function listMessages(int $groupId)
    {
        return $this->messageRepository->listMessages($groupId);
    }
}
