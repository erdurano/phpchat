<?php

namespace App\Controllers;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\ModelExceptions\ResourceAlreadyExists;
use App\Models\ModelInterface;
use App\Models\UserModel;

use function PHPUnit\Framework\isEmpty;

class MembersController
{
    use ControllerTrait;

    private ?GroupController $group_resource = null;

    public function __construct(ModelInterface $model = null)
    {
        if (is_null($model)) {
            $this->model = new UserModel();
        } else {
            $this->model = $model;
        }
        if (is_null($this->group_resource)) {
            $this->group_resource = new GroupController();
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

        $query_array = [
            'user_name' => $request_data['user_name'],
            'group_id' => $args['id']
        ];
        try {
            $returned_data = $this->model->createResource($query_array);
            $return_response = $response->withStatus(StatusCodeInterface::STATUS_CREATED);

            $return_response->getBody()
                ->write(json_encode($returned_data));
        } catch (ResourceAlreadyExists $e) {
            $return_response = $response->withStatus(StatusCodeInterface::STATUS_CONFLICT);
            $return_response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        }

        return $return_response;
    }

    public function GET(Request $request, Response $response, array $args): Response
    {

        if (!empty($args)) {

            $returned_data = $this->model->getResource($args);
            $returned_data["messages_url"] = sprintf('/groups/%d/messages', $returned_data["id"]);
        } else {
            $returned_data = $this->model->getResource([]);
        }


        $response->getBody()->write(json_encode($returned_data));

        return $response;
    }
}
