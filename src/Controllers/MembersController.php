<?php

namespace App\Controllers;

use App\Models\ModelExceptions\ResourceNotFound;
use App\Services\GroupService;
use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Services\ServiceExceptions\AlreadyMember;
use App\Services\ServiceInterface;
use App\Services\MemberService;


class MembersController
{
    use ControllerTrait;

    private MemberService $service;

    public function __construct(MemberService $service = null)
    {
        if (is_null($service)) {
            $this->service = new MemberService();
        } else {
            $this->service = $service;
        }
    }

    public function POST(Request $request, Response $response, array $args): Response
    {

        $request_data = json_decode($request->getBody()->__toString(), associative: true);

        if (array_keys($request_data) != ['user_name']) {

            $err_response = $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);

            $err_response->getBody()->write(json_encode([
                'error' => 'Malformed request. Request should have form of:\n' .
                    '[\n\t"user_name": string\n]'
            ]));
            return $err_response;
        }

        $user_name = $request_data['user_name'];
        $group_id = $args['group_id'];

        try {
            $user_array = $this->service->subscribeUserToGroup($user_name, $group_id);
            $return_response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);

            $return_response->getBody()
                ->write(json_encode($user_array));
        } catch (AlreadyMember $e) {
            $return_response = $response->withStatus(StatusCodeInterface::STATUS_CONFLICT);
            $return_response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $return_response;
    }

    public function GET(Request $request, Response $response, array $args): Response
    {

        try {

            $returned_data = $this->service->getMembersByGroupId($args['group_id']);
            $returned_data["messages_url"] = sprintf('/groups/%d/messages', $returned_data["id"]);


            $response->getBody()->write(json_encode($returned_data));

            return $response;
        } catch (ResourceNotFound $e) {
            $response->getBody()->write(json_encode(["error" => "This group is empty"]));
            return $response->withStatus(StatusCodeInterface::STATUS_NOT_FOUND);
        }
    }
}
