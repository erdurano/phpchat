<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\MessageService;
use Fig\Http\Message\StatusCodeInterface;

class MessageController
{
    use ControllerTrait;

    private MessageService $service;

    public function __construct(MessageService $service = null)
    {
        if (is_null($service)) {
            $this->service = new MessageService();
        } else {
            $this->service = $service;
        }
    }

    public function POST(Request $request, Response $response, array $args): Response
    {
        $data = json_decode($request->getBody(), associative: true);
        $groupId = $args['group_id'];
        if (!array_key_exists('user_name', $data) | !array_key_exists('message', $data)) {
            $response->getBody()->write(json_encode(
                [
                    'error' => 'Malformed request. Request should have form of:\n' .
                        '[\n\t"user_name": string,\n\t"message: string\n]'

                ]
            ));
            return $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
        }
        $result = $this->service->sendMessage($groupId, $data['user_name'], $data['message']);

        $return_response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);
        $return_response->getBody()->write(json_encode($result));
        return $return_response;
    }

    public function GET(Request $request, Response $response, array $args): Response
    {
        $groupId = $args['group_id'];
        if (array_key_exists('since', $args)) {
            $since = $args['since'];
        } else {
            $since = null;
        }
        $messages = $this->service->listMessages($groupId, $since);

        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
