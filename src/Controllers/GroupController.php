<?php

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\GroupModel;
use App\Models\ModelInterface;
use App\Services\GroupService;
use App\Services\ServiceExceptions\GroupAlreadyExists;

use function PHPUnit\Framework\isNull;

class GroupController
{
    use ControllerTrait;

    private GroupService $service;


    public function __construct(GroupService $service = null)
    {
        if (is_null($service)) {
            $this->service = new GroupService();
        } else {
            $this->service = $service;
        }
    }

    public function POST(Request $request, Response $response, array $args): Response
    {

        $requestData = json_decode($request->getBody()->__toString(), associative: true);
        try {
            if (array_keys($requestData) != ['group_name']) {
                $response = $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST);
                $response->getBody()->write(
                    json_encode(
                        [
                            'error' => 'Malformed request.',
                            'request_schema' => ["group_name" => "string"]
                        ]
                    )
                );
                return $response;
            }

            $returned_data = $this->service->createGroup($requestData['group_name']);
            if (!empty($returned_data)) {
                $response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);
                $response->getBody()->write(json_encode($returned_data));
            };
        } catch (GroupAlreadyExists $e) {

            $error_message = ['error' => $e->getMessage()];
            $response = $response->withStatus(StatusCodeInterface::STATUS_CONFLICT);
            $response->getBody()->write(json_encode($error_message));
        }
        return $response;
    }

    public function GET(Request $request, Response $response, array $args): Response
    {

        if (!empty($args)) {
            $extra_params = array_diff(['id'], array_keys($args));
            if (!empty($extra_params)) {
                return $response->withStatus(StatusCodeInterface::STATUS_BAD_REQUEST)
                    ->getBody()
                    ->write(
                        json_encode([
                            'error' => sprintf(
                                "Invalid query parameters. %s not allowed as query parameters.",
                                implode(", ", $extra_params)
                            )
                        ])
                    );
            }

            // Check what kind of json i should return for singular resource.

            $returned_data = $this->service->getGroupById($args['id']);
            $returned_data["members_url"] = sprintf('/groups/%d/members', $returned_data["id"]);
            $returned_data["messages_url"] = sprintf('/groups/%d/messages', $returned_data["id"]);
        } else {
            $returned_data = $this->service->getGroups();
        }


        $response->getBody()->write(json_encode($returned_data));

        return $response;
    }
}
