<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\GroupService;

class GroupController
{
    private $groupService;

    public function __construct()
    {
        $this->groupService = new GroupService();
    }

    public function createGroup(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $result = $this->groupService->createGroup($data['group_name']);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function listGroups(Request $request, Response $response): Response
    {
        $groups = $this->groupService->listGroups();

        $response->getBody()->write(json_encode($groups));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function joinGroup(Request $request, Response $response, array $args): Response
    {
        $data = $request->getParsedBody();
        $groupId = (int) $args['group_id'];
        $userId = $data['user_id'];

        $result = $this->groupService->joinGroup($groupId, $userId);

        $response->getBody()->write(json_encode($result));
        return $response->withHeader('Content-Type', 'application/json');
    }
}
