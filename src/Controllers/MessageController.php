<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\MessageService;

class MessageController
{
    private $messageService;

    public function __construct()
    {
        $this->messageService = new MessageService();
    }

    public function sendMessage(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $groupId = (int) $args['group_id'];
        $result = $this->messageService->sendMessage($groupId, $data['user_id'], $data['message']);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listMessages(Request $request, Response $response, array $args): Response
    {
        $groupId = (int) $args['group_id'];
        $messages = $this->messageService->listMessages($groupId);

        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
