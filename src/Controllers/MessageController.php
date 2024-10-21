<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\MessageService;
use DateTimeImmutable;
use Fig\Http\Message\StatusCodeInterface;

use function PHPUnit\Framework\isNull;

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
        if (is_null($data)) {
            $data = [];
        }
        $groupId = $args['group_id'];
        if (!array_key_exists('user_name', $data) | !array_key_exists('message', $data)) {
            $response->getBody()->write(json_encode(
                [
                    'error' => 'Malformed request.',
                    'request_schema' =>
                    [
                        "user_name" => 'string type',
                        'message' => 'string type'
                    ]

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

        $query_params = $request->getQueryParams();
        if (array_key_exists('since', $query_params)) {
            $since = DateTimeImmutable::createFromFormat('Y-m-d-H-i-s', $query_params['since']);
            if (!$since) {
                $response->getBody()->write(json_encode(
                    [
                        'error' => "'since' query parameter should adhere to 'YYYY-MM-DD-hh-mm-ss' format."
                    ]
                ));
                return $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
            }
            $messages = $this->service->listMessages($groupId, $since);
        } else {
            $messages = $this->service->listMessages($groupId, null);
        };

        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
